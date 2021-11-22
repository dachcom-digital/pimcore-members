<?php

namespace MembersBundle\CoreExtension;

use MembersBundle\Adapter\Group\GroupInterface;
use Pimcore\Model\DataObject\ClassDefinition\Data\PreGetDataInterface;
use Pimcore\Model\DataObject\ClassDefinition\Data\PreSetDataInterface;
use Pimcore\Model\Element;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\ClassDefinition\Data\Relations\AbstractRelations;
use Pimcore\Model\DataObject\ClassDefinition\Data\QueryResourcePersistenceAwareInterface;
use Pimcore\Model\DataObject\ClassDefinition\Data\Extension;
use Pimcore\Normalizer\NormalizerInterface;

class GroupMultiselect extends AbstractRelations implements
    QueryResourcePersistenceAwareInterface,
    PreGetDataInterface,
    PreSetDataInterface,
    NormalizerInterface
{
    use Extension\QueryColumnType;

    /**
     * @internal
     */
    public int $width = 0;

    /**
     * @internal
     */
    public int $height = 0;

    /**
     * @internal
     */
    public string $renderType = 'list';

    /**
     * @var string
     */
    public $fieldtype = 'membersGroupMultiselect';

    /**
     * @var string
     */
    public $queryColumnType = 'text';

    /**
     * @var bool
     */
    public $relationType = true;

    public function setRenderType(string $renderType): self
    {
        $this->renderType = $renderType;

        return $this;
    }

    public function getRenderType(): string
    {
        return $this->renderType;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function setWidth(mixed $width): self
    {
        $this->width = is_numeric($width) ? (int) $width : 0;

        return $this;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function setHeight(mixed $height): self
    {
        $this->height = is_numeric($height) ? (int) $height : 0;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterTypeDeclaration(): ?string
    {
        return 'array';
    }

    /**
     * {@inheritdoc}
     */
    public function getReturnTypeDeclaration(): ?string
    {
        return '?array';
    }

    /**
     * {@inheritdoc}
     */
    public function getPhpdocInputType(): ?string
    {
        return $this->getPhpdocType();
    }

    /**
     * {@inheritdoc}
     */
    public function getPhpdocReturnType(): ?string
    {
        return $this->getPhpdocType();
    }

    /**
     * {@inheritdoc}
     */
    protected function getPhpdocType()
    {
        return sprintf('array|\%s[]', GroupInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    public function rewriteIds($container, $idMapping, $params = [])
    {
        $data = $this->getDataFromObjectParam($container, $params);

        return $this->rewriteIdsService($data, $idMapping);
    }

    /**
     * {@inheritdoc}
     */
    public function getDataForEditmode($data, $object = null, $params = [])
    {
        $returnData = [];

        if (!is_array($data)) {
            return [];
        }

        foreach ($data as $el) {
            if ($el instanceof Element\ElementInterface) {
                $returnData[] = [
                    'id'   => $el->getId(),
                    'name' => method_exists($el, 'getName') ? $el->getName() : $el->getKey()
                ];
            } elseif (is_numeric($el)) {
                $returnData[] = [
                    'id'   => $el,
                    'name' => $el
                ];
            }
        }

        return $returnData;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataFromEditmode($data, $object = null, $params = [])
    {
        //if not set, return empty null
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
     * {@inheritdoc}
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
        }

        if (is_array($data) && count($data) === 0) {
            return '';
        }

        throw new \Exception('invalid data passed to getDataForQueryResource - must be array');
    }

    /**
     * {@inheritdoc}
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
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function loadData($data, $object = null, $params = [])
    {
        $elements = [
            'dirty' => false,
            'data'  => []
        ];

        if (is_array($data)) {
            foreach ($data as $element) {
                $e = null;
                if ($element['type'] === 'object') {
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
     * {@inheritdoc}
     */
    public function prepareDataForPersistence($data, $object = null, $params = [])
    {
        $return = [];

        if (is_array($data) && count($data) > 0) {
            $counter = 1;
            foreach ($data as $rowObject) {
                if ($rowObject instanceof Element\ElementInterface) {
                    $return[] = [
                        'dest_id'   => $rowObject->getId(),
                        'type'      => Element\Service::getElementType($rowObject),
                        'fieldname' => $this->getName(),
                        'index'     => $counter
                    ];
                }
                $counter++;
            }

            return $return;
        }

        if (is_array($data) && count($data) === 0) {
            //give empty array if data was not null
            return [];
        }

        //return null if data was null - this indicates data was not loaded
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($value, $params = [])
    {
        if (is_array($value)) {
            $result = [];
            foreach ($value as $element) {
                $type = Element\Service::getElementType($element);
                $id = $element->getId();
                $result[] = [
                    'type' => $type,
                    'id'   => $id,
                ];
            }

            return $result;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($value, $params = [])
    {
        if (is_array($value)) {
            $result = [];
            foreach ($value as $elementData) {
                $type = $elementData['type'];
                $id = $elementData['id'];
                $element = Element\Service::getElementById($type, $id);
                if ($element) {
                    $result[] = $element;
                }
            }

            return $result;
        }

        return null;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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

        return 'no preview';
    }

    /**
     * {@inheritdoc}
     */
    public function getForCsvExport($object, $params = [])
    {
        $data = $this->getDataFromObjectParam($object, $params);

        if (is_array($data)) {
            return implode(',', $data);
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getDataForSearchIndex($object, $params = [])
    {
        $data = $this->getDataFromObjectParam($object, $params);

        if (is_array($data)) {
            return implode(' ', $data);
        }

        return '';
    }

    /**
     * @param array                    $data
     * @param null|DataObject\Concrete $object
     * @param mixed                    $params
     *
     * @return array
     * @see Data::getDataFromEditmode
     *
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
     * @return array
     */
    public function getDataForGrid($data, $object = null, $params = [])
    {
        return $this->getDataForEditmode($data, $object, $params);
    }
}
