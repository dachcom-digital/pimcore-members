<?php

namespace MembersBundle\CoreExtension;

use Pimcore\Model\Element;
use Pimcore\Model\DataObject;

class GroupMultiselect extends DataObject\ClassDefinition\Data\Relations\AbstractRelations
{
    /**
     * Static type of this element.
     *
     * @var string
     */
    public $fieldtype = 'membersGroupMultiselect';

    /**
     * @var bool
     */
    public $relationType = true;

    /**
     * @param string $data
     * @param null   $object
     * @param array  $params
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
     * @see DataObject\ClassDefinition\Data::getDataFromEditmode
     *
     * @param array                          $data
     * @param null|DataObject\AbstractObject $object
     * @param mixed                          $params
     * @return array
     */
    public function getDataFromEditmode($data, $object = null, $params = [])
    {
        //if not set, return null
        if ($data === null or $data === false) {
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
     * @param       $data
     * @param null  $object
     * @param array $params
     * @return array|null
     */
    public function getDataForResource($data, $object = null, $params = [])
    {
        $return = [];

        if (is_array($data) && count($data) > 0) {
            $counter = 1;
            foreach ($data as $group) {
                $return[] = [
                    'src_id'    => $object->getId(),
                    'dest_id'   => $group->getId(),
                    'type'      => 'object',
                    'fieldname' => $this->getName(),
                    'index'     => $counter
                ];

                $counter++;
            }

            return $return;
        } elseif (is_array($data) and count($data) === 0) {
            //give empty array if data was not null
            return [];
        } else {
            //return null if data was null  - this indicates data was not loaded
            return null;
        }
    }

    /**
     * @param       $data
     * @param null  $object
     * @param array $params
     * @return null|string
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
     * @param       $object
     * @param array $params
     * @return array|mixed|null
     */
    public function preGetData($object, $params = [])
    {
        $data = $object->{$this->getName()};
        return is_array($data) ? $data : [];
    }

    /**
     * @param array $data
     * @param null  $object
     * @param array $params
     * @return array
     */
    public function getDataFromResource($data = [], $object = null, $params = [])
    {
        $elements = [];
        if (is_array($data) && count($data) > 0) {
            foreach ($data as $element) {
                $e = null;
                if ($element['type'] == 'object') {
                    $e = DataObject::getById($element['dest_id']);
                }
                if ($e instanceof Element\ElementInterface) {
                    $elements[] = $e;
                }
            }
        }

        return $elements;
    }

}