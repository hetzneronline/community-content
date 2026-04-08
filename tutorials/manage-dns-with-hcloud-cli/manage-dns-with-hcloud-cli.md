---
SPDX-License-Identifier: MIT
path: "/tutorials/manage-dns-with-hcloud-cli"
slug: "manage-dns-with-hcloud-cli"
date: "2026-04-08"
title: "Managing Hetzner DNS with the hcloud CLI"
short_description: "Learn how to create DNS zones, manage records (A, MX, TXT, DKIM), and set up a new domain on Hetzner DNS using only the hcloud CLI."
tags: ["DNS", "hcloud", "CLI", "Domain"]
author: "Ramon"
author_link: "https://github.com/"
author_img: ""
author_description: ""
language: "en"
available_languages: ["en"]
header_img: ""
cta: "cloud"
---

# Managing Hetzner DNS with the hcloud CLI

## Introduction

Hetzner provides a DNS service that can be managed through the [Hetzner DNS Console](https://dns.hetzner.com) or via an API. However, if you're already using the `hcloud` CLI to manage your cloud infrastructure, you can manage DNS zones and records through the same tool — no need to switch between different interfaces or authentication methods.

This tutorial covers:

- Creating a DNS zone for a new domain
- Adding common record types: A, MX, TXT (SPF, DKIM, site verification)
- Updating and deleting records
- A complete setup workflow for a new domain with Google Workspace mail

**Prerequisites:**

- The `hcloud` CLI installed and configured ([official guide](https://community.hetzner.com/tutorials/howto-hcloud-cli/))
- A domain you want to manage via Hetzner DNS
- A Hetzner Cloud API token with DNS permissions

> **Note:** The Hetzner DNS REST API (`dns.hetzner.com`) uses a **different authentication token** than the Cloud API. When using the `hcloud` CLI, always use your Cloud API token — the CLI handles routing to the correct API internally.

---

## Step 1 - Set up your API token

Store your Hetzner Cloud API token in a file for easy reuse:

```bash
echo "your-api-token" > ~/.hetzner-token
chmod 600 ~/.hetzner-token
```

Then prefix all `hcloud` commands with your token:

```bash
HCLOUD_TOKEN=$(cat ~/.hetzner-token) hcloud <command>
```

To avoid repeating this, you can export the variable for your session:

```bash
export HCLOUD_TOKEN=$(cat ~/.hetzner-token)
```

---

## Step 2 - Create a DNS zone

Create a new DNS zone for your domain:

```bash
HCLOUD_TOKEN=$(cat ~/.hetzner-token) hcloud zone create --name example.com
```

Verify the zone was created:

```bash
HCLOUD_TOKEN=$(cat ~/.hetzner-token) hcloud zone list
```

---

## Step 3 - Add DNS records

### A records

Point your domain and `www` subdomain to your server IP:

```bash
# Root domain
HCLOUD_TOKEN=$(cat ~/.hetzner-token) hcloud zone rrset create \
  --name "@" --type A --record "1.2.3.4" --ttl 3600 example.com

# www subdomain
HCLOUD_TOKEN=$(cat ~/.hetzner-token) hcloud zone rrset create \
  --name "www" --type A --record "1.2.3.4" --ttl 3600 example.com
```

Replace `1.2.3.4` with your server's IP address.

### MX records (Google Workspace)

```bash
HCLOUD_TOKEN=$(cat ~/.hetzner-token) hcloud zone rrset create \
  --name "@" --type MX \
  --record "1 aspmx.l.google.com." \
  --record "5 alt1.aspmx.l.google.com." \
  --record "5 alt2.aspmx.l.google.com." \
  --record "10 alt3.aspmx.l.google.com." \
  --record "10 alt4.aspmx.l.google.com." \
  --ttl 3600 example.com
```

### SPF record (Google Workspace)

```bash
HCLOUD_TOKEN=$(cat ~/.hetzner-token) hcloud zone rrset create \
  --name "@" --type TXT \
  --record '"v=spf1 include:_spf.google.com ~all"' \
  --ttl 3600 example.com
```

### Site verification (Google)

Add the verification token from Google Admin Console:

```bash
HCLOUD_TOKEN=$(cat ~/.hetzner-token) hcloud zone rrset create \
  --name "@" --type TXT \
  --record '"google-site-verification=your-token-here"' \
  --ttl 3600 example.com
```

---

## Step 4 - List and verify records

```bash
HCLOUD_TOKEN=$(cat ~/.hetzner-token) hcloud zone rrset list example.com
```

---

## Step 5 - Update a record

The `hcloud` CLI does not support updating an existing RRSet in place. To update a record, delete the existing RRSet and recreate it:

```bash
# Delete existing TXT RRSet
HCLOUD_TOKEN=$(cat ~/.hetzner-token) hcloud zone rrset delete example.com "@" TXT

# Recreate with updated values
HCLOUD_TOKEN=$(cat ~/.hetzner-token) hcloud zone rrset create \
  --name "@" --type TXT \
  --record '"v=spf1 include:_spf.google.com ~all"' \
  --record '"google-site-verification=your-token-here"' \
  --ttl 3600 example.com
```

> **Important:** All TXT records for the same name must be in a single `rrset create` call. If you delete and recreate, include all values in one command.

---

## Step 6 - Add DKIM record (Google Workspace)

DKIM public keys often exceed the 255-character limit for a single DNS string. You need to split the value into chunks of at most 255 characters.

First, get your DKIM key from Google Admin Console (Admin → Apps → Google Workspace → Gmail → Authenticate email). Copy the full `p=` value.

Then split it using Python:

```python
val = "v=DKIM1; k=rsa; p=<your-full-key-here>"
print(f'Part 1 ({len(val[:255])} chars): {val[:255]}')
print(f'Part 2 ({len(val[255:])} chars): {val[255:]}')
```

Add the record with both parts as separate quoted strings in one `--record` flag:

```bash
HCLOUD_TOKEN=$(cat ~/.hetzner-token) hcloud zone rrset create \
  --name "google._domainkey" --type TXT \
  --record '"v=DKIM1; k=rsa; p=<first-255-chars>" "<remaining-chars>"' \
  --ttl 3600 example.com
```

---

## Step 7 - Set nameservers at your registrar

Once all records are in place, point your domain to Hetzner's nameservers at your domain registrar:

```
helium.ns.hetzner.de
hydrogen.ns.hetzner.com
oxygen.ns.hetzner.com
```

DNS propagation typically takes a few minutes to a few hours.

---

## Conclusion

You've learned how to manage Hetzner DNS zones and records entirely from the `hcloud` CLI. This approach keeps your DNS workflow consistent with the rest of your Hetzner infrastructure management.

A typical setup order for a new domain is:

1. Create zone
2. Add A records (`@` and `www`)
3. Add MX records
4. Add SPF TXT record
5. Add Google site verification TXT record
6. Set nameservers at registrar
7. After DNS propagation: add DKIM record (requires Google Admin activation first)

---

## Using this as an AI Agent skill

This tutorial is also available as a `SKILL.md` file for use with AI agents (e.g. Claude Code). It contains the same commands in a compact reference format, ready to drop into your agent skills directory.

[View the SKILL.md on GitHub Gist](https://gist.github.com/ramon-webdevpro-nl/73e2389b89fcd632c91eac3b7b87b359)

##### License: MIT
