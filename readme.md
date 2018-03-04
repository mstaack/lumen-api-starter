# Lumen 5.6. API Starter with JWT

# Installation
- git clone
- composer install
- copy `env.example` to `.env` and add app&jwt keys

- Also consider running `composer meta` when adding models for better autocompletion

# Routes
```
➜  lumen-api-starter git:(master) ✗ ./artisan route:list
+--------+----------------+-----------------+-----------------------------------------+---------+------------+
| Verb   | Path           | NamedRoute      | Controller                              | Action  | Middleware |
+--------+----------------+-----------------+-----------------------------------------+---------+------------+
| POST   | /auth/login    | auth.login      | App\Http\Controllers\AuthController     | login   |            |
| GET    | /auth/user     | auth.user       | App\Http\Controllers\AuthController     | getUser | auth       |
| GET    | /articles      | articles.index  | App\Http\Controllers\ArticlesController | index   | auth       |
| GET    | /articles/{id} | articles.find   | App\Http\Controllers\ArticlesController | find    | auth       |
| POST   | /articles      | articles.create | App\Http\Controllers\ArticlesController | create  | auth       |
| PUT    | /articles/{id} | articles.update | App\Http\Controllers\ArticlesController | update  | auth       |
| DELETE | /articles/{id} | articles.delete | App\Http\Controllers\ArticlesController | delete  | auth       |
+--------+----------------+-----------------+-----------------------------------------+---------+------------+
```
