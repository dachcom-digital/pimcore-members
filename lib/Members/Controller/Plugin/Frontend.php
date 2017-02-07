<?php

namespace Members\Controller\Plugin;

use Pimcore\Model\Document\Page;
use Pimcore\Model\Object;
use Members\Tool\Observer;
use Members\Model\Configuration;

class Frontend extends \Zend_Controller_Plugin_Abstract
{
    /**
     * @var null
     */
    private static $renderer = NULL;

    /**
     * @param \Zend_Controller_Request_Abstract $request
     */
    public function preDispatch(\Zend_Controller_Request_Abstract $request)
    {
        parent::preDispatch($request);

        self::$renderer = \Zend_Controller_Action_HelperBroker::getExistingHelper('ViewRenderer');
        self::$renderer->initView();

        $view = self::$renderer->view;
        $view->addHelperPath(PIMCORE_PLUGINS_PATH . '/Members/lib/Members/View/Helper', 'Members\View\Helper');

        //allow website to use own scripts
        $view->addScriptPath(PIMCORE_PLUGINS_PATH . '/Members/views/scripts');
        $view->addScriptPath(PIMCORE_PLUGINS_PATH . '/Members/views/layouts');

        if ($request->getParam('pimcore_request_source') === 'staticroute') {
            if ($view instanceof \Pimcore\View) {
                //is it a object related view? check if object is in core.settings.object.allowed.
                //if so, trigger event, to allow custom routing restriction!
                //if event is empty, add "default" to m:groups and allow document view!.
                $objectRestriction = ['default'];
                $boundedObject = NULL;

                $boEvent = \Pimcore::getEventManager()->trigger('members.restriction.object', NULL, ['params' => $request->getParams()]);

                if ($boEvent->count()) {
                    $returnData = $boEvent->last();
                    if ($returnData instanceof Object\AbstractObject) {
                        $boundedObject = $returnData;
                        $objectRestriction = Observer::getObjectRestrictedGroups($boundedObject);
                    }
                }

                if (!is_null($boundedObject)) {
                    $this->handleDocumentAuthentication($boundedObject);
                }

                $view->headMeta()->appendName('m:groups', implode(',', $objectRestriction), []);
            }
        } else if ($request->getParam('document') instanceof Page) {
            $document = $request->getParam('document');

            $groups = Observer::getDocumentRestrictedGroups($document);
            $view->headMeta()->appendName('m:groups', implode(',', $groups), []);

            $this->handleDocumentAuthentication($request->getParam('document'));
        }
    }

    /**
     * @param Page|Object\AbstractObject $element
     *
     * @return bool
     */
    private function handleDocumentAuthentication($element)
    {
        if (Observer::isAdmin()) {
            return FALSE;
        }

        //@fixme: does not work in backend? :)
        if (!\Pimcore\Tool::isFrontend()) {
            return FALSE;
        }

        //now load restriction and redirect user to login page, if page is restricted!
        if ($element instanceof Object\AbstractObject) {
            $restrictedType = Observer::isRestrictedObject($element);
        } else {
            $restrictedType = Observer::isRestrictedDocument($element);
        }

        if ($restrictedType['section'] == Observer::SECTION_ALLOWED) {
            return FALSE;
        }

        if ($restrictedType['state'] == Observer::STATE_LOGGED_IN && $restrictedType['section'] == Observer::SECTION_ALLOWED) {
            return FALSE;
        }

        //do not check /members pages, they will check them itself.
        $requestUrl = $this->getRequest()->getRequestUri();
        $nowAllowed = [
            Configuration::getLocalizedPath('routes.login'),
            Configuration::getLocalizedPath('routes.profile')
        ];

        foreach ($nowAllowed as $not) {
            if (substr($requestUrl, 0, strlen($not)) == $not) {
                return FALSE;
            }
        }

        if (in_array($this->getRequest()->getRequestUri(), $nowAllowed)) {
            return FALSE;
        }

        if ($restrictedType['state'] === Observer::STATE_LOGGED_IN && $restrictedType['section'] === Observer::SECTION_NOT_ALLOWED) {
            $url = Configuration::getLocalizedPath('routes.profile');
        } else {
            $url = sprintf('%s?back=%s',
                Configuration::getLocalizedPath('routes.login'),
                urlencode($this->getRequest()->getRequestUri())
            );
        }

        $this->getResponse()->setRedirect($url)->sendResponse();
        exit;
    }
}