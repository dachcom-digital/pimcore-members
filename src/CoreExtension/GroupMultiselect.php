<?php

namespace MembersBundle\CoreExtension;

use MembersBundle\Adapter\Group\GroupInterface;
use Pimcore\Model\DataObject\ClassDefinition\Data\PreGetDataInterface;
use Pimcore\Model\DataObject\ClassDefinition\Data\PreSetDataInterface;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\DataObject\Fieldcollection\Data\AbstractData;
use Pimcore\Model\DataObject\Localizedfield;
use Pimcore\Model\Element;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\ClassDefinition\Data\Relations\AbstractRelations;
use Pimcore\Model\DataObject\ClassDefinition\Data\QueryResourcePersistenceAwareInterface;
use Pimcore\Normalizer\NormalizerInterface;

class GroupMultiselect extends AbstractRelations implements
    QueryResourcePersistenceAwareInterface,
    PreGetDataInterface,
    PreSetDataInterface,
    NormalizerInterface
{
    public int $width = 0;
    public int $height = 0;
    public string $renderType = 'list';
    public bool $relationType = true;

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

    public function getFieldType(): string
    {
        return 'membersGroupMultiselect';
    }

    public function getQueryColumnType(): array|string
    {
        return $this->getColumnType();
    }

    public function getColumnType(): string
    {
        return 'text';
    }

    public function getParameterTypeDeclaration(): ?string
    {
        return 'array';
    }

    public function getReturnTypeDeclaration(): ?string
    {
        return '?array';
    }

    public function getPhpdocInputType(): ?string
    {
        return $this->getPhpdocType();
    }

    public function getPhpdocReturnType(): ?string
    {
        return $this->getPhpdocType();
    }

    protected function getPhpdocType(): string
    {
        return sprintf('array|\%s[]', GroupInterface::class);
    }

    public function rewriteIds(mixed $container, array $idMapping, array $params = []): mixed
    {
        $data = $this->getDataFromObjectParam($container, $params);

        return $this->rewriteIdsService($data, $idMapping);
    }

    public function getDataForEditmode(mixed $data, DataObject\Concrete $object = null, array $params = []): mixed
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

    public function getDataFromEditmode(mixed $data, DataObject\Concrete $object = null, array $params = []): mixed
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

    public function getDataForQueryResource(mixed $data, Concrete $object = null, array $params = []): mixed
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

    public function preGetData(mixed $container, array $params = []): mixed
    {
        $data = $container->getObjectVar($this->getName());
        if (!$container->isLazyKeyLoaded($this->getName())) {
            $data = $this->load($container, ['force' => true]);

            $container->setObjectVar($this->getName(), $data);
            $this->markLazyloadedFieldAsLoaded($container);

            if ($container instanceof Element\DirtyIndicatorInterface) {
                $container->markFieldDirty($this->getName(), false);
            }
        }

        return is_array($data) ? $data : [];
    }

    public function preSetData(mixed $container, mixed $data, array $params = []): mixed
    {
        if ($data === null) {
            $data = [];
        }

        $this->markLazyloadedFieldAsLoaded($container);

        return $data;
    }

    protected function loadData(array $data, Localizedfield|AbstractData|\Pimcore\Model\DataObject\Objectbrick\Data\AbstractData|Concrete $object = null, array $params = []): mixed
    {
        $elements = [
            'dirty' => false,
            'data'  => []
        ];

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

        return $elements;
    }

    protected function prepareDataForPersistence(array|Element\ElementInterface $data, Localizedfield|AbstractData|\Pimcore\Model\DataObject\Objectbrick\Data\AbstractData|Concrete $object = null, array $params = []): mixed
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

        if (is_array($data)) {
            //give empty array if data was not null
            return [];
        }

        //return null if data was null - this indicates data was not loaded
        return null;
    }

    public function normalize(mixed $value, array $params = []): mixed
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

    public function denormalize(mixed $value, array $params = []): mixed
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

    public function resolveDependencies(mixed $data): array
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

    public function getVersionPreview(mixed $data, DataObject\Concrete $object = null, array $params = []): string
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

    public function getForCsvExport(DataObject\Concrete|DataObject\Localizedfield|DataObject\Objectbrick\Data\AbstractData|DataObject\Fieldcollection\Data\AbstractData $object, array $params = []): string
    {
        $data = $this->getDataFromObjectParam($object, $params);

        if (is_array($data)) {
            return implode(',', $data);
        }

        return '';
    }

    public function getDataForSearchIndex(DataObject\Localizedfield|DataObject\Fieldcollection\Data\AbstractData|DataObject\Objectbrick\Data\AbstractData|DataObject\Concrete $object, array $params = []): string
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
