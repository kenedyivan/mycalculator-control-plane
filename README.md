# MyCalculator Control Plane - Full Laravel Project

This is a runnable Laravel control plane demo for provisioning `mycalculator` tenant environments.

## Run

```bash
unzip mycalculator-control-plane-full.zip
cd mycalculator-control-plane-full
docker compose up -d --build
```

Open:

```text
http://localhost:8310
```

Health check:

```text
http://localhost:8310/health
```

## What it includes

- Full Laravel project structure
- Tenant list/create/show pages
- JSON tenant registry in `storage/app/tenants.json`
- `php artisan tenant:provision {tenantId}` command
- Deployment templates for a tenant VM
- SSH/SCP provisioning flow for Multipass testing

## Create a Multipass tenant VM

```bash
multipass launch 22.04 --name calc-tenant-1 --cpus 1 --memory 1G --disk 10G
multipass shell calc-tenant-1
```

Inside the VM:

```bash
sudo apt update
sudo apt install -y docker.io docker-compose-plugin
sudo usermod -aG docker ubuntu
exit
```

Restart:

```bash
multipass restart calc-tenant-1
multipass info calc-tenant-1
```

Use the VM IP as `Target Host` in the control plane.

## Provision tenant

After creating a tenant in the UI, run the command shown on the tenant page:

```bash
docker compose exec control-plane php artisan tenant:provision <tenant_id>
```

Render deployment files only:

```bash
docker compose exec control-plane php artisan tenant:provision <tenant_id> --no-ssh
```

## Important

The included tenant Nginx template assumes the `mycalculator` image serves HTTP on port `8080`.

For production Mertrix, replace SSH with AWS SSM and replace JSON storage with MySQL/PostgreSQL + queued jobs.
