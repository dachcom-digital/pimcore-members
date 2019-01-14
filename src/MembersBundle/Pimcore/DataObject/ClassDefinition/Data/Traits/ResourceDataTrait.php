<?php

namespace MembersBundle\Pimcore\DataObject\ClassDefinition\Data\Traits;

if (interface_exists(\Pimcore\Model\DataObject\ClassDefinition\Data\ResourcePersistenceAwareInterface::class)) {
    trait ResourceDataTrait
    {
    }
} else {
    trait ResourceDataTrait
    {
        /**
         * @param       $data
         * @param null  $object
         * @param array $params
         *
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
    }
}