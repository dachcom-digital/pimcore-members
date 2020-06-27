<?php

namespace MembersBundle\CoreExtension;

use Pimcore\Model\Element;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\ClassDefinition\Data\Relations\AbstractRelations;
use Pimcore\Model\DataObject\ClassDefinition\Data\QueryResourcePersistenceAwareInterface;
use Pimcore\Model\DataObject\ClassDefinition\Data\Extension;

class GroupMultiselect extends AbstractRelations implements QueryResourcePersistenceAwareInterface
{
    use Extension\QueryColumnType;

    /**
     * @var string
     */
    public $fieldtype = 'membersGroupMultiselect';

    /**
     * @var string
     */
    public $queryColumnType = 'text';

    /**
     * @var string
     */
    public $phpdocType = 'array';

    /**
     * @var bool
     */
    public $relationType = true;

    /**
     * @param array $data
     * @param null  $object
     * @param array $params
     *
     * @return string
     */
    public function getDataForEditmode($data, $object = null, $params = [])
    {
        $returnIds = [];

        if (is_array($data)) {
            foreach ($data as $el) {
                if ($el instanceof Element\ElementInterface) {
                    $returnIds[] = $el->getId();
                } else { //keep BC!
                    $returnIds[] = $el;
                }
            }
        }

        return implode(',', $returnIds);
    }

    /**
     * @param array                          $data
     * @param null|DataObject\AbstractObject $object
     * @param mixed                          $params
     *
     * @return array
     *
     * @see DataObject\ClassDefinition\Data::getDataFromEditmode
     */
    public function getDataFromEditmode($data, $object = null, $params = [])
    {
        //if not set, return null
        if ($data === null || $data === false) {
            return null;
        }

        $elements = [];
        if (is_array($data) && count($data) > 0) {
            foreach ($data as $groupId) {
                $e = DataObject::getById($groupId);
                if ($e instanceof Element\ElementInterface) {
                    $elements[] = $e;
                }
            }
        }

        //must return array if data shall be set
        return $elements;
    }

    /**
     * @param array $data
     * @param null  $object
     * @param array $params
     *
     * @return null|string
     *
     * @throws \Exception
     */
    public function getDataForQueryResource($data, $object = null, $params = [])
    {
        //return null when data is not set
        if (!$data) {
            return null;
        }

        $d = [];

        if (is_array($data) && count($data) > 0) {
            foreach ($data as $element) {
                if ($element instanceof Element\ElementInterface) {
                    $elementType = Element\Service::getElementType($element);
                    $d[] = $elementType . '|' . $element->getId();
                }
            }

            return ',' . implode(',', $d) . ',';
        } elseif (is_array($data) && count($data) === 0) {
            return '';
        } else {
            throw new \Exception('invalid data passed to getDataForQueryResource - must be array');
        }
    }

    /**
     * @param DataObject\Concrete $object
     * @param array               $params
     *
     * @return array
     *
     * @throws \Exception
     */
    public function preGetData($object, $params = [])
    {
        $data = $object->getObjectVar($this->getName());
        if (!$object->isLazyKeyLoaded($this->getName())) {
            $data = $this->load($object, ['force' => true]);

            $object->setObjectVar($this->getName(), $data);
            $this->markLazyloadedFieldAsLoaded($object);

            if ($object instanceof Element\DirtyIndicatorInterface) {
                $object->markFieldDirty($this->getName(), false);
            }
        }

        return is_array($data) ? $data : [];
    }

    /**
     * @param DataObject\Concrete $object
     * @param array|null          $data
     * @param array               $params
     *
     * @return array|null
     */
    public function preSetData($object, $data, $params = [])
    {
        if ($data === null) {
            $data = [];
        }

        $this->markLazyloadedFieldAsLoaded($object);

        return $data;
    }

    /**
     * @param array $data
     * @param null  $object
     * @param array $params
     *
     * @return array
     */
    public function loadData($data, $object = null, $params = [])
    {
        $elements = [
            'dirty' => false,
            'data'  => []
        ];

        if (is_array($data) && count($data) > 0) {
            foreach ($data as $element) {
                $e = null;
                if ($element['type'] == 'object') {
                    $e = DataObject::getById($element['dest_id']);
                }
                if ($e instanceof Element\ElementInterface) {
                    $elements['data'][] = $e;
                } else {
                    $elements['dirty'] = true;
                }
            }
        }

        return $elements;
    }

    /**
     * @param array $data
     * @param null  $object
     * @param array $params
     *
     * @return array
     */
    public function prepareDataForPersistence($data, $object = null, $params = [])
    {
        $return = [];

        if (is_array($data) && count($data) > 0) {
            $counter = 1;
            foreach ($data as $object) {
                if ($object instanceof Element\ElementInterface) {
                    $return[] = [
                        'dest_id'   => $object->getId(),
                        'type'      => Element\Service::getElementType($object),
                        'fieldname' => $this->getName(),
                        'index'     => $counter
                    ];
                }
                $counter++;
            }

            return $return;
        } elseif (is_array($data) and count($data) === 0) {
            //give empty array if data was not null
            return [];
        } else {
            //return null if data was null - this indicates data was not loaded
            return null;
        }
    }

    /**
     * @param array           $data
     * @param null|DataObject $object
     * @param mixed           $params
     *
     * @return array
     */
    public function getDataFromGridEditor($data, $object = null, $params = [])
    {
        return $this->getDataFromEditmode($data, $object, $params);
    }

    /**
     * @param array|null      $data
     * @param null|DataObject $object
     * @param array           $params
     *
     * @return string
     */
    public function getDataForGrid($data, $object = null, $params = [])
    {
        return $this->getDataForEditmode($data, $object, $params);
    }

    /**
     * @param null|DataObject[] $data
     *
     * @return array
     */
    public function resolveDependencies($data)
    {
        $dependencies = [];

        if (is_array($data) && count($data) > 0) {
            foreach ($data as $e) {
                if ($e instanceof Element\ElementInterface) {
                    $elementType = Element\Service::getElementType($e);
                    $dependencies[$elementType . '_' . $e->getId()] = [
                        'id'   => $e->getId(),
                        'type' => $elementType
                    ];
                }
            }
        }

        return $dependencies;
    }

    /**
     * @param array|null      $data
     * @param null|DataObject $object
     * @param array           $params
     *
     * @return string|null
     *
     * @see Data::getVersionPreview
     */
    public function getVersionPreview($data, $object = null, $params = [])
    {
        if (is_array($data) && count($data) > 0) {
            $paths = [];
            foreach ($data as $o) {
                if ($o instanceof Element\ElementInterface) {
                    $paths[] = $o->getRealFullPath();
                }
            }

            return implode('<br />', $paths);
        }

        return null;
    }
}
