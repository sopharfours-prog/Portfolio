# Sophar — Personal Portfolio

The website and its admin dashboard live in [`portfolio-app/`](portfolio-app/README.md) (Laravel + MySQL).
See that README for installation, running and deployment.

## Quick access (local development)

| What | Link |
| --- | --- |
| Website | http://localhost:8000 |
| Admin dashboard | http://localhost:8000/admin |
| Admin login | http://localhost:8000/admin/login |
| Visitor login / sign up | http://localhost:8000/login · http://localhost:8000/register |
| Member profile | http://localhost:8000/account |

Admin email addresses and passwords are **not** stored in this public repository.
Keep them in `ACCESS.local.md` next to this file (git-ignored, never uploaded), or reset a password from
the admin login page ("Forgot password?").

## Start

```bash
cd portfolio-app
docker compose up -d   # database (MySQL)
composer serve         # http://localhost:8000 (allows uploads up to 100 MB)
```
