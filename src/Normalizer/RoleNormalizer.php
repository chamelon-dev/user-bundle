<?php

namespace Pantheon\UserBundle\Normalizer;

use Pantheon\UserBundle\Entity\Role;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class RoleNormalizer implements NormalizerInterface
{
    public function normalize($object, $format = null, array $context = [])
    {
        $result = [];
        if ($object instanceof Role) {
            $result['id'] = $object->getId();
            $result['name'] = $object->getName();
            $result['title'] = $object->getTitle();
            $result['description'] = $object->getDescription();
            $result['created_at']  = (($createdAt = $object->getCreatedAt())
                ? $createdAt->format('Y-m-d H:i:s')
                : null
            );
            $result['updated_at']  = (($updatedAt = $object->getUpdatedAt())
                ? $updatedAt->format('Y-m-d H:i:s')
                : null
            );
        }
        return $result;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Role;
    }
}