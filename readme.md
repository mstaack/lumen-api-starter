# Lumen 5.6. API Starter with JWT

# Installation
- git clone
- composer install
- copy `env.example` to `.env` and add app&jwt keys
- add sqlite database with `touch database/database.sqlite` (You probably dont want to run sqlite in production)
- run migrations & seeders with `artisan migrate --seed`
- A default user is created during seeding: `demo@demo.com` / `password`
- To quickly start aserver run `./artisan serve`
- Also consider running `composer meta` when adding models for better autocompletion

# Routes
```
➜  lumen-api-starter git:(master) ✗ ./artisan route:list
+--------+----------------+-----------------+-----------------------------------------+----------+------------+
| Verb   | Path           | NamedRoute      | Controller                              | Action   | Middleware |
+--------+----------------+-----------------+-----------------------------------------+----------+------------+
| GET    | /              |                 | None                                    | Closure  |            |
| POST   | /auth/register | auth.register   | App\Http\Controllers\AuthController     | register |            |
| POST   | /auth/login    | auth.login      | App\Http\Controllers\AuthController     | login    |            |
| GET    | /auth/user     | auth.user       | App\Http\Controllers\AuthController     | getUser  | auth       |
| GET    | /articles      | articles.index  | App\Http\Controllers\ArticlesController | index    | auth       |
| GET    | /articles/{id} | articles.find   | App\Http\Controllers\ArticlesController | find     | auth       |
| POST   | /articles      | articles.create | App\Http\Controllers\ArticlesController | create   | auth       |
| PUT    | /articles/{id} | articles.update | App\Http\Controllers\ArticlesController | update   | auth       |
| DELETE | /articles/{id} | articles.delete | App\Http\Controllers\ArticlesController | delete   | auth       |
+--------+----------------+-----------------+-----------------------------------------+----------+------------+
```

# Make Commands
```
  lumen-api-starter git:(master) ✗ ./artisan  | grep make
 make
  make:command         Create a new Artisan command
  make:controller      Create a new controller class
  make:event           Create a new event class
  make:job             Create a new job class
  make:listener        Create a new event listener class
  make:mail            Create a new email class
  make:middleware      Create a new middleware class
  make:migration       Create a new migration file
  make:model           Create a new Eloquent model class
  make:policy          Create a new policy class
  make:provider        Create a new service provider class
  make:resource        Create a new resource
  make:seeder          Create a new seeder class
  make:test            Create a new test class
```
