<?php

namespace App\Action\User;

use App\Table\QueryParams;
use App\Table\Table;
use App\Table\User\UserTable;

readonly class ListUsers
{

    public function __construct(
        private UserTable $userTable
    ) {
    }

    public function execute(QueryParams $params): Table
    {
        return $this->userTable->create($params);
    }
}
