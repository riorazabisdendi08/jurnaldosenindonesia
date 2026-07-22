# Environment Variables — Jurnal Dosen Indonesia

This project expects sensitive values to be provided via environment variables rather than stored in source files.

Required variables

- `SERPAPI_API_KEY` — (required) Your SerpApi API key used by `api/search.php`.

Optional variables

- `LOG_ADMIN_USER` — Admin username for simple log viewer (default `admin`).
- `LOG_ADMIN_PASS` — Admin password for log viewer (recommended to set).
- `SITE_JDI_API_KEY` — Per-site API key for the "Jurnal Dosen Indonesia" site configuration.

Examples

Unix / macOS (bash):

```bash
export SERPAPI_API_KEY="a97bcc0992909512b60caf62afc72efc3dce572a2d73998364258099867868ea"
export LOG_ADMIN_USER="admin"
export LOG_ADMIN_PASS="strongpassword"
export SITE_JDI_API_KEY="site_specific_key"
```

Windows (PowerShell):

```powershell
$env:SERPAPI_API_KEY = "a97bcc0992909512b60caf62afc72efc3dce572a2d73998364258099867868ea"
$env:LOG_ADMIN_USER  = "admin"
$env:LOG_ADMIN_PASS  = "strongpassword"
$env:SITE_JDI_API_KEY = "site_specific_key"
```

Running the environment check

From project root:

```bash
php scripts/check_env.php
```

Notes

- Do not commit real secrets into the repository. Use `config/config.example.php` as a template only.
- If a secret has been committed in the past, rotate/replace it in the provider (SerpApi dashboard) and update your environment.
- Ensure `logs/` is not world-readable and is excluded from version control (already added to `.gitignore`).
