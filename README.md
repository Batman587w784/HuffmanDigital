# Huffman Digital — Client Sites

This repository mirrors **Hostinger `public_html`** 1:1. On every push to `main`, hPanel Git copies the repo tree into `public_html`.

## Layout

```
repo-root/
  digital/back/index.php   # password-gated client directory
  zuls/                    # example client folder
    index.html             # Huffman Digital preview wrapper (do not edit per client)
    site.html              # client website (edit this)
  _template/               # scaffold for new clients
  new-site.sh              # creates a new client folder
```

## Per-client convention

- `index.html` — identical Huffman Digital wrapper (`iframe src="site.html"`). Never edit per client.
- `site.html` — the client's actual website. Must include a real `<title>` with the company name.

## New client

```bash
./new-site.sh "Client Business Name"
```

Edit the new folder's `site.html`, preview locally, then commit and push to `main`.

## Backend

`digital/back/index.php` lists all client folders. Change `PASSWORD` before go-live. Add system folders to `$HIDE` so they are not listed as clients.

## Deploy workflow

1. Edit locally in VS Code
2. Preview with Live Preview or open files in a browser
3. Commit and push to `main` → Hostinger auto-deploys in ~1 minute

## Warning

The first hPanel Git pull **overwrites** `public_html` with this repo. Any file not in the repo is removed from the live site.
