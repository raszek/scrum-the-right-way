<?php

namespace App\Table\User;

use App\Repository\User\UserRepository;
use App\Table\Column\ActionColumn;
use App\Table\QueryParams;
use App\Table\Table;
use App\Table\TableColumn;
use App\Table\TableDefinition;
use App\Table\TableFactory;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

readonly class UserTable
{

    public function __construct(
        private UserRepository $userRepository,
        private TableFactory $tableFactory,
        private Environment $twig
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

        $definition->addColumn(new ActionColumn(
            field: 'id',
            label: 'Actions',
            formatCallback: fn(UserRecord $user) => $this->getActions($user->id)
        ));

        return $this->tableFactory->create($definition, $queryParams);
    }

    /**
     * @param string $id
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    private function getActions(string $id): string
    {
        $actions = [
            [
                'label' => 'Edit',
                'route' => 'app_admin_user_edit',
                'routeParameters' => ['id' => $id]
            ]
        ];

        return $this->twig->render('common/table/action_column.html.twig', [
            'actions' => $actions,
        ]);
    }

}
