<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class InjectionController extends AbstractController
{
    private function createTable(Connection $connection): void
    {
        $schemaManager = $connection->createSchemaManager();

        if ($schemaManager->tablesExist('messages')) {
            return;
        }

        $sql = "
            CREATE TABLE messages (
                id INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                username VARCHAR(255),
                message VARCHAR(255)
            )
        ";

        $connection->executeStatement($sql);
    }

    #[Route('/', name: 'home')]
    public function index(Connection $connection): Response
    {
        $schemaManager = $connection->createSchemaManager();

        $tableExists = $schemaManager->tablesExist('messages');

        $messages = [];

        if ($tableExists) {
            $messages = $connection
                ->executeQuery('SELECT * FROM messages ORDER BY id')
                ->fetchAllAssociative();
        }

        return $this->render('injection/index.html.twig', [
            'tableExists' => $tableExists,
            'messages' => $messages
        ]);
    }

    #[Route('/create-table', name: 'create_table')]
    public function createTableAction(
        Connection $connection
    ): Response {
        $this->createTable($connection);

        return $this->redirectToRoute('home');
    }

    #[Route(
        '/insert',
        name: 'insert_message',
        methods: ['POST']
    )]
    public function insert(
        Request $request,
        Connection $connection
    ): Response {
        $this->createTable($connection);

        $username = $request->request->get('username');
        $message = $request->request->get('message');

        $sql = "
            INSERT INTO messages (username, message)
            VALUES ('$username', '$message')
        ";

        try {
            $connection->executeStatement($sql);

            $this->addFlash(
                'success',
                'Query executed.'
            );
        } catch (\Throwable $e) {
            $this->addFlash(
                'error',
                'SQL error: ' . $e->getMessage()
            );
        }

        return $this->redirectToRoute('home');
    }
}