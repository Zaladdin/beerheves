<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Repositories\UserRepository;
use App\Services\LogService;

final class AuthController extends Controller
{
    private UserRepository $users;
    private LogService $logs;

    public function __construct($db, $request, $config)
    {
        parent::__construct($db, $request, $config);
        $this->users = new UserRepository($db);
        $this->logs = new LogService($db);
    }

    public function showLogin(): void
    {
        $this->render('auth/login', [
            'title' => 'Вход',
        ], 'guest');
    }

    public function login(): void
    {
        $this->ensureCsrf();

        $data = [
            'username' => trim((string) $this->request->post('username')),
            'password' => (string) $this->request->post('password'),
        ];

        $errors = Validator::validate($data, [
            'username' => ['required', 'max:100'],
            'password' => ['required', 'max:255'],
        ], [
            'username' => 'Логин',
            'password' => 'Пароль',
        ]);

        if ($errors !== []) {
            $this->redirectWithErrors('/login', $errors, ['username' => $data['username']]);
        }

        $user = $this->users->findByUsername($data['username']);

        if (!$user || $user['status'] !== 'active' || !$this->verifyPassword($data['password'], $user['password_hash'])) {
            Session::flash('error', 'Неверный логин или пароль.');
            Session::flash('old', ['username' => $data['username']]);
            $this->redirect('/login');
        }

        Auth::login($user);
        $this->logs->write((int) $user['id'], 'login', 'user', (int) $user['id'], [
            'username' => $user['username'],
            'role' => $user['role'],
        ]);

        Session::flash('success', 'Вход выполнен.');
        $this->redirect('/');
    }

    public function logout(): void
    {
        $this->ensureCsrf();
        Auth::logout();
        Session::flash('success', 'Сессия завершена.');
        $this->redirect('/login');
    }

    private function verifyPassword(string $password, string $storedHash): bool
    {
        if (str_starts_with($storedHash, '$')) {
            return password_verify($password, $storedHash);
        }

        $parts = explode('$', $storedHash);

        if (count($parts) !== 4 || $parts[0] !== 'pbkdf2_sha256') {
            return false;
        }

        $iterations = (int) $parts[1];
        $salt = base64_decode($parts[2], true);
        $expectedHash = $parts[3];

        if ($salt === false || $iterations <= 0) {
            return false;
        }

        $derived = base64_encode(hash_pbkdf2('sha256', $password, $salt, $iterations, 32, true));

        return hash_equals($expectedHash, $derived);
    }
}
