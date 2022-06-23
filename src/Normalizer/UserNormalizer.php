<?php

namespace Pantheon\UserBundle\Normalizer;

use Pantheon\UserBundle\Entity\User;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UserNormalizer implements NormalizerInterface
{
    public function normalize($object, $format = null, array $context = [])
    {
        $result = [];
        if ($object instanceof User) {
            $result = [
                'id' => $object->getId(),
                'username' => $object->getUsername(),
                'email' => $object->getEmail(),
                'is_active' => $object->isActive(),
                'created_at' => (($createdAt = $object->getCreatedAt())
                    ? $createdAt->format('Y-m-d H:i:s')
                    : null
                ),
                'updated_at' =>  (($updatedAt = $object->getUpdatedAt())
                    ? $updatedAt->format('Y-m-d H:i:s')
                    : null
                ),
                'lastname' => $object->getLastname(),
                'name' => $object->getName(),
                'patronymic' => $object->getPatronymic(),
                'workplace' => $object->getWorkplace(),
                'duty' => $object->getDuty(),
                'phone' => $object->getPhone(),
            ];
        }
        return $result;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof User;
    }
}