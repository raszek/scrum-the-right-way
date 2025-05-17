<?php

namespace App\Repository\QueryBuilder;

use App\Service\Common\SqidService;

class QueryBuilder extends \Doctrine\ORM\QueryBuilder
{
    public function sqidParameter(string $key, string $sqid): static
    {
        $this->setParameter($key, $sqid, 'sqid');

        return $this;
    }

    public function in(string $field, array $values): static
    {
        if (empty($values)) {
            return $this;
        }

        $this->andWhere($this->expr()->in($field, $values));

        return $this;
    }

    public function notIn(string $field, array $values): static
    {
        if (empty($values)) {
            return $this;
        }

        $this->andWhere($this->expr()->notIn($field, $values));

        return $this;
    }

    public function fuzzyLike(string $parameter, string $search): static
    {
        $parts = explode(' ', $search);

        foreach ($parts as $i => $part) {
            $this->andWhere($parameter.' LIKE :s'.$i);
            $this->setParameter('s'.$i, '%'.$part.'%');
        }

        return $this;
    }

    public function sqidsParameter(string $key, array $sqids): static
    {
        $ids = SqidService::create()->decodeMany($sqids);

        $this->setParameter($key, $ids);

        return $this;
    }

    public function searchParameter(string $field, ?string $value): static
    {
        $this->setParameter($field, '%'.mb_strtolower($value).'%');

        return $this;
    }
}
