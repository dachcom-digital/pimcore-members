<?php

namespace DachcomBundle\Test\Helper;

use Codeception\Module;
use Codeception\TestInterface;
use DachcomBundle\Test\Util\FileGeneratorHelper;
use DachcomBundle\Test\Util\MembersHelper;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\Document\Email;
use Pimcore\Model\Document\Page;
use Pimcore\Model\Document\Snippet;
use Pimcore\Model\Document\Tag\Checkbox;
use Pimcore\Model\Staticroute;
use Pimcore\Model\Tool\Email\Log;
use Pimcore\Model\Document\Tag\Areablock;
use Pimcore\Model\Document\Tag\Relation;
use Pimcore\Tests\Helper\ClassManager;
use Pimcore\Tests\Util\TestHelper;
use Pimcore\Translation\Translator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Serializer\Serializer;

class PimcoreBackend extends Module
{
    /**
     * @param TestInterface $test
     */
    public function _before(TestInterface $test)
    {
        FileGeneratorHelper::preparePaths();
        parent::_before($test);
    }

    /**
     * @param TestInterface $test
     */
    public function _after(TestInterface $test)
    {
        MembersHelper::cleanUp();
        FileGeneratorHelper::cleanUp();
        MembersHelper::reCreateMembersStructure();

        parent::_after($test);
    }

    /**
     * Actor Function to create a Page Document
     *
     * @param string      $documentKey
     * @param null|string $action
     * @param null|string $controller
     * @param null|string $locale
     *
     * @return Page
     */
    public function haveAPageDocument(
        $documentKey = 'members-test',
        $action = null,
        $controller = null,
        $locale = 'en'
    ) {
        $document = $this->generatePageDocument($documentKey, $action, $controller, $locale);

        try {
            $document->save();
        } catch (\Exception $e) {
            \Codeception\Util\Debug::debug(sprintf('[MEMBERS ERROR] error while saving document page. message was: ' . $e->getMessage()));
        }

        $this->assertInstanceOf(Page::class, Page::getById($document->getId()));

        return $document;
    }

    /**
     * Actor Function to create a Snippet
     *
     * @param string $snippetKey
     * @param array  $elements
     * @param string $locale
     *
     * @return null|Snippet
     */
    public function haveASnippetDocument($snippetKey, $elements = [], $locale = 'en')
    {
        $snippet = $this->generateSnippetDocument($snippetKey, $elements, $locale);

        try {
            $snippet->save();
        } catch (\Exception $e) {
            \Codeception\Util\Debug::debug(sprintf('[MEMBERS ERROR] error while saving document snippet. message was: ' . $e->getMessage()));
        }

        $this->assertInstanceOf(Snippet::class, $snippet);

        return $snippet;
    }

    /**
     * Actor Function to create a mail document for given type
     *
     * @param        $type
     * @param array  $mailParams
     * @param string $locale
     *
     * @return Email
     */
    public function haveAEmailDocumentForType($type, array $mailParams = [], $locale = 'en')
    {
        $emailDocument = $mailTemplate = $this->generateEmailDocument(sprintf('email-%s', $type), $mailParams, $locale);
        $this->assertInstanceOf(Email::class, $emailDocument);

        return $emailDocument;
    }

    /**
     * Actor function to generate a dummy asset file.
     *
     * @param     $fileName
     * @param int $fileSizeInMb Mb
     */
    public function haveFile($fileName, $fileSizeInMb = 1)
    {
        FileGeneratorHelper::generateDummyFile($fileName, $fileSizeInMb);
    }

    /**
     * Actor function to see a generated dummy file in download directory.
     *
     * @param $fileName
     */
    public function seeDownload($fileName)
    {
        $supportDir = FileGeneratorHelper::getDownloadPath();
        $filePath = $supportDir . $fileName;

        $this->assertTrue(is_file($filePath));
    }

    /**
     * Actor Function to place a members area on a document
     *
     * @param Page         $document
     * @param null|Page    $redirectAfterSuccessDocument
     * @param null|Snippet $loginSnippet
     * @param bool         $hideAreaAfterLogin
     */
    public function seeAMembersAreaElementPlacedOnDocument(Page $document, $redirectAfterSuccessDocument = null, $loginSnippet = null, $hideAreaAfterLogin = false)
    {
        $areaElement = $this->createMembersArea($redirectAfterSuccessDocument, $loginSnippet, $hideAreaAfterLogin);
        $document->setElements($areaElement);

        try {
            $document->save();
        } catch (\Exception $e) {
            \Codeception\Util\Debug::debug(sprintf('[MEMBERS ERROR] error while saving document. message was: ' . $e->getMessage()));
        }

        $this->assertCount(count($areaElement), $document->getElements());
    }

    /**
     * Actor Function to see if given email has been sent
     *
     * @param Email $email
     */
    public function seeEmailIsSent(Email $email)
    {
        $this->assertInstanceOf(Email::class, $email);

        $foundEmails = $this->getEmailsFromDocumentIds([$email->getId()]);
        $this->assertEquals(1, count($foundEmails));
    }

    /**
     * Actor Function to see if an email has been sent to admin
     *
     * @param Email $email
     */
    public function seeEmailIsNotSent(Email $email)
    {
        $this->assertInstanceOf(Email::class, $email);

        $foundEmails = $this->getEmailsFromDocumentIds([$email->getId()]);
        $this->assertEquals(0, count($foundEmails));
    }

    /**
     * Actor Function to see if admin email contains given properties
     *
     * @param Email $mail
     * @param array $properties
     */
    public function seePropertiesInEmail(Email $mail, array $properties)
    {
        $this->assertInstanceOf(Email::class, $mail);

        $foundEmails = $this->getEmailsFromDocumentIds([$mail->getId()]);
        $this->assertGreaterThan(0, count($foundEmails));

        $serializer = $this->getSerializer();

        foreach ($foundEmails as $email) {
            $params = $serializer->decode($email->getParams(), 'json', ['json_decode_associative' => true]);
            foreach ($properties as $propertyKey => $propertyValue) {
                $key = array_search($propertyKey, array_column($params, 'key'));
                if ($key === false) {
                    $this->fail(sprintf('Failed asserting that mail params array has the key "%s".', $propertyKey));
                }

                $data = $params[$key];
                $this->assertEquals($propertyValue, $data['data']['value']);
            }
        }
    }

    /**
     * Actor Function to see if admin email contains given properties
     *
     * @param Email $mail
     * @param array $properties
     */
    public function seePropertyKeysInEmail(Email $mail, array $properties)
    {
        $this->assertInstanceOf(Email::class, $mail);

        $foundEmails = $this->getEmailsFromDocumentIds([$mail->getId()]);
        $this->assertGreaterThan(0, count($foundEmails));

        $serializer = $this->getSerializer();

        foreach ($foundEmails as $email) {
            $params = $serializer->decode($email->getParams(), 'json', ['json_decode_associative' => true]);
            foreach ($properties as $propertyKey) {
                $key = array_search($propertyKey, array_column($params, 'key'));
                $this->assertNotSame(false, $key);
            }
        }
    }

    /**
     * Actor Function to see if admin email not contains given properties
     *
     * @param Email $mail
     * @param array $properties
     */
    public function cantSeePropertyKeysInEmail(Email $mail, array $properties)
    {
        $this->assertInstanceOf(Email::class, $mail);

        $foundEmails = $this->getEmailsFromDocumentIds([$mail->getId()]);
        $this->assertGreaterThan(0, count($foundEmails));

        $serializer = $this->getSerializer();

        foreach ($foundEmails as $email) {
            $params = $serializer->decode($email->getParams(), 'json', ['json_decode_associative' => true]);
            foreach ($properties as $propertyKey) {
                $this->assertFalse(
                    array_search(
                        $propertyKey,
                        array_column($params, 'key')),
                    sprintf('Failed asserting that search for "%s" is false.', $propertyKey)
                );
            }
        }
    }

    /**
     * Actor Function to see rendered body text in given email
     *
     * @param Email  $mail
     * @param string $string
     */
    public function seeInRenderedEmailBody(Email $mail, string $string)
    {
        $this->assertInstanceOf(Email::class, $mail);

        $foundEmails = $this->getEmailsFromDocumentIds([$mail->getId()]);
        $this->assertGreaterThan(0, count($foundEmails));

        $serializer = $this->getSerializer();

        foreach ($foundEmails as $email) {
            $params = $serializer->decode($email->getParams(), 'json', ['json_decode_associative' => true]);

            $bodyKey = array_search('body', array_column($params, 'key'));
            $this->assertNotSame(false, $bodyKey);

            $data = $params[$bodyKey];
            $this->assertContains($string, $data['data']['value']);
        }
    }

    /**
     * Actor Function to see if a key has been stored in admin translations
     *
     * @param string $key
     *
     */
    public function seeKeyInFrontendTranslations(string $key)
    {
        /** @var Translator $translator */
        $translator = \Pimcore::getContainer()->get('pimcore.translator');
        $this->assertTrue($translator->getCatalogue()->has($key));
    }

    /**
     * Actor Function to generate a single static route.
     *
     * @param string $name
     *
     * @return Staticroute
     */
    public function haveAStaticRoute(string $name = 'test_route')
    {
        $data = [
            'id'               => 1,
            'name'             => $name,
            'pattern'          => '/(\\w+)\\/members-test-route\\/(\\d+)$/',
            'reverse'          => '/%_locale/members-test-route/%object_id',
            'module'           => 'AppBundle',
            'controller'       => '@AppBundle\\Controller\\DefaultController',
            'action'           => 'staticRoute',
            'variables'        => '_locale,object_id',
            'defaults'         => null,
            'siteId'           => [],
            'priority'         => 0,
            'legacy'           => false,
            'creationDate'     => 1545383519,
            'modificationDate' => 1545383619
        ];

        $route = new Staticroute();
        $route->setValues($data);
        $route->save();

        $this->assertInstanceOf(Staticroute::class, $route);

        return $route;
    }

    /**
     * Actor Function to generate a pimcore class from json definition file.
     *
     * @param string $name
     *
     * @throws \Codeception\Exception\ModuleException
     * @return ClassDefinition
     */
    public function haveAPimcoreClass(string $name = 'TestClass')
    {
        $cm = $this->getClassManager();

        $bundleClass = getenv('DACHCOM_BUNDLE_HOME');
        $path = $bundleClass . '/_etc/config/bundle/pimcore';

        $class = $cm->setupClass($name, sprintf('%s/%s.json', $path, $name));
        $this->assertInstanceOf(ClassDefinition::class, $class);

        return $class;
    }

    /**
     * API Function to get sent email ids from given document ids
     *
     * @public to allow usage from other modules
     *
     * @param array $documentIds
     *
     * @return Log[]
     */
    public function getEmailsFromDocumentIds(array $documentIds)
    {
        $emailLogs = new Log\Listing();
        $emailLogs->addConditionParam(sprintf('documentId IN (%s)', implode(',', $documentIds)));

        return $emailLogs->load();
    }

    /**
     * API Function to get pimcore serializer
     *
     * @public to allow usage from other modules
     * @return Serializer
     *
     */
    public function getSerializer()
    {
        $serializer = null;

        try {
            $serializer = $this->getContainer()->get('pimcore_admin.serializer');
        } catch (\Exception $e) {
            \Codeception\Util\Debug::debug(sprintf('[MEMBERS ERROR] error while getting pimcore admin serializer. message was: ' . $e->getMessage()));
        }

        $this->assertInstanceOf(Serializer::class, $serializer);

        return $serializer;
    }

    /**
     * API Function to create a Snippet
     *
     * @param        $snippetKey
     * @param array  $elements
     * @param string $locale
     *
     * @return null|Snippet
     */
    protected function generateSnippetDocument($snippetKey, $elements = [], $locale = 'en')
    {
        $document = new Snippet();
        $document->setController('@AppBundle\Controller\DefaultController');
        $document->setAction('snippet');
        $document->setType('snippet');
        $document->setElements($elements);
        $document->setParentId(1);
        $document->setUserOwner(1);
        $document->setUserModification(1);
        $document->setCreationDate(time());
        $document->setKey($snippetKey);
        $document->setProperty('language', 'text', $locale, false, 1);
        $document->setPublished(true);

        return $document;

    }

    /**
     * API Function to create a page document
     *
     * @param string      $key
     * @param null|string $action
     * @param null|string $controller
     * @param string      $locale
     *
     * @return Page
     */
    protected function generatePageDocument($key = 'members-test', $action = null, $controller = null, $locale = 'en')
    {
        $action = is_null($action) ? 'default' : $action;
        $controller = is_null($controller) ? '@AppBundle\Controller\DefaultController' : $controller;

        $document = TestHelper::createEmptyDocumentPage('', false);
        $document->setController($controller);
        $document->setAction($action);
        $document->setKey($key);
        $document->setProperty('language', 'text', $locale, false, 1);

        return $document;
    }

    /**
     * API Function to create a email document
     *
     * @param string $key
     * @param array  $params
     *
     * @return null|Email
     */
    protected function generateEmailDocument($key = 'members-test-email', array $params = [])
    {
        $documentKey = uniqid(sprintf('%s-', $key));

        $document = new Email();
        $document->setType('email');
        $document->setParentId(1);
        $document->setUserOwner(1);
        $document->setUserModification(1);
        $document->setCreationDate(time());
        $document->setModule('MembersBundle');
        $document->setController('@MembersBundle\Controller\EmailController');
        $document->setAction('email');
        $document->setKey($documentKey);

        $to = 'recpient@test.org';
        if (isset($params['to'])) {
            $to = $params['to'];
        }

        $subject = sprintf('MEMBERS EMAIL %s', $documentKey);
        if (isset($params['subject'])) {
            $subject = $params['subject'];
        }

        $document->setTo($to);
        $document->setSubject($subject);

        if (isset($params['replyTo'])) {
            $document->setReplyTo($params['replyTo']);
        }

        if (isset($params['cc'])) {
            $document->setCc($params['cc']);
        }

        if (isset($params['bcc'])) {
            $document->setBcc($params['bcc']);
        }

        if (isset($params['from'])) {
            $document->setFrom($params['from']);
        }

        if (isset($params['properties'])) {
            $document->setProperties($params['properties']);
        }

        try {
            $document->save();
        } catch (\Exception $e) {
            \Codeception\Util\Debug::debug(sprintf('[MEMBERS ERROR] error while creating email. message was: ' . $e->getMessage()));
            return null;
        }

        return $document;
    }

    /**
     * API Function to create a members area element.
     *
     * @param null|Page    $redirectAfterSuccessDocument
     * @param null|Snippet $loginSnippet
     * @param bool         $hideAreaAfterLogin
     *
     * @return array
     */
    protected function createMembersArea($redirectAfterSuccessDocument = null, $loginSnippet = null, $hideAreaAfterLogin = false)
    {
        $blockArea = new Areablock();
        $blockArea->setName(MembersHelper::AREA_TEST_NAMESPACE);

        $redirectAfterSuccess = null;
        if ($redirectAfterSuccessDocument instanceof Page) {
            $redirectAfterSuccess = new Relation();
            $redirectAfterSuccess->setName(sprintf('%s:1.redirectAfterSuccess', MembersHelper::AREA_TEST_NAMESPACE));
            $data = [
                'id'      => $redirectAfterSuccessDocument->getId(),
                'type'    => 'document',
                'subtype' => $redirectAfterSuccessDocument->getType()
            ];
            $redirectAfterSuccess->setDataFromEditmode($data);
        }

        $hideWhenLoggedIn = new Checkbox();
        $hideWhenLoggedIn->setName(sprintf('%s:1.hideWhenLoggedIn', MembersHelper::AREA_TEST_NAMESPACE));
        $hideWhenLoggedIn->setDataFromEditmode($hideAreaAfterLogin);

        $showSnippedWhenLoggedIn = null;
        if ($loginSnippet instanceof Snippet) {
            $showSnippedWhenLoggedIn = new Relation();
            $showSnippedWhenLoggedIn->setName(sprintf('%s:1.showSnippedWhenLoggedIn', MembersHelper::AREA_TEST_NAMESPACE));

            $data2 = [
                'id'      => $loginSnippet->getId(),
                'type'    => 'document',
                'subtype' => $loginSnippet->getType()
            ];

            $showSnippedWhenLoggedIn->setDataFromEditmode($data2);
        }

        $blockArea->setDataFromEditmode([
            [
                'key'    => '1',
                'type'   => 'members_login',
                'hidden' => false
            ]
        ]);

        $data = [
            sprintf('%s', MembersHelper::AREA_TEST_NAMESPACE)                    => $blockArea,
            sprintf('%s:1.hideWhenLoggedIn', MembersHelper::AREA_TEST_NAMESPACE) => $hideWhenLoggedIn
        ];

        if ($redirectAfterSuccess !== null) {
            $data[sprintf('%s:1.redirectAfterSuccess', MembersHelper::AREA_TEST_NAMESPACE)] = $redirectAfterSuccess;
        }

        if ($showSnippedWhenLoggedIn !== null) {
            $data[sprintf('%s:1.showSnippedWhenLoggedIn', MembersHelper::AREA_TEST_NAMESPACE)] = $showSnippedWhenLoggedIn;
        }

        return $data;
    }

    /**
     * @return Container
     * @throws \Codeception\Exception\ModuleException
     */
    protected function getContainer()
    {
        return $this->getModule('\\' . PimcoreCore::class)->getContainer();
    }

    /**
     * @return Module|ClassManager
     * @throws \Codeception\Exception\ModuleException
     */
    protected function getClassManager()
    {
        return $this->getModule('\\' . ClassManager::class);
    }
}
