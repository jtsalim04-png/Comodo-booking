# Live ticket updates (Mercure) on Railway

When a user buys a ticket on the website, admins on the mobile app receive an instant update via **Mercure** (Server-Sent Events over HTTPS).

## Flow

1. Website `POST /orders/{id}/purchase` or API `POST /api/tickets` → `TicketPurchaseService`
2. Service publishes `ticket.purchased` to Mercure topic  
   `{DEFAULT_URI}/topics/admin/tickets`
3. Admin mobile app (logged in as `ROLE_ADMIN`) subscribes via SSE and refetches dashboard/events

## Railway setup

Deploy a **Mercure** service (official image `dunglas/mercure`) and set these variables on your **Symfony** service:

| Variable | Example |
|----------|---------|
| `DEFAULT_URI` | `https://webdevcomodo-production-8ae6.up.railway.app` |
| `MERCURE_URL` | Internal publish URL, e.g. `http://mercure:80` or public hub URL |
| `MERCURE_PUBLIC_URL` | Public hub URL, e.g. `https://mercure-xxx.up.railway.app` |
| `MERCURE_JWT_SECRET` | Same secret on Symfony **and** Mercure (`!ChangeThisMercureJWTSecret!`) |

Mercure container env (minimal):

```env
MERCURE_PUBLISHER_JWT_KEY=!ChangeThisMercureJWTSecret!
MERCURE_SUBSCRIBER_JWT_KEY=!ChangeThisMercureJWTSecret!
MERCURE_EXTRA_DIRECTIVES=cors_origins *
```

Use a strong random secret in production.

## Verify

1. `GET https://your-app.up.railway.app/api/realtime/config` → `"enabled": true`
2. Log in as admin on the phone
3. Buy a ticket on the website as a user
4. Admin app should show a notification and refresh stats without pull-to-refresh

If Mercure is not configured, the mobile app **polls every 12 seconds** as a fallback.
