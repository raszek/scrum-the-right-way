<?php

namespace App\Table\User;

use App\Entity\Project\Project;
use App\Entity\Sprint\Sprint;
use App\Repository\Sprint\SprintRepository;
use App\Repository\User\UserRepository;
use App\Table\QueryParams;
use App\Table\Table;
use App\Table\TableColumn;
use App\Table\TableDefinition;
use App\Table\TableFactory;

readonly class UserTable
{

    public function __construct(
        private UserRepository $userRepository,
        private TableFactory $tableFactory,
    ) {
    }

    public function create(QueryParams $queryParams): Table
    {
        $query = $this->userRepository->listUserQuery();

        $definition = new TableDefinition($query);

        $definition->addColumn(new TableColumn(
            field: 'email',
            label: 'Email',
            sortField: 'user.email',
        ));

        $definition->addColumn(new TableColumn(
            field: 'fullName',
            label: 'Name',
            sortField: 'fullName',
        ));

        return $this->tableFactory->create($definition, $queryParams);
    }

}
