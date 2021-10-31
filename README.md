# farm.army - Frontend

Track your farming and pool performance on the Binance Chain, Polygon, Fantom, KuCoin Community Chain, Harmony, Celo, Moonriver

## Tech Stack

 - PHP 8 + Symfony
 - node.js + npm (Webpack, Symfony encore)
 - Vue.js 2.x (needs migration to 3.x)
 - jQuery (to be replaced)
 - Bootstrap 5

### Business Values

 - Expected requests: 1-2 per second
 - Backend is using external APIs, which have rate limits. So basic no massive calls are allowed
 - Backend is behind a HTTP Loadbalancer, consider when calling 

## Install

Install PHP and node.js packages

```
composer install
npm install
```

### Token Icons

Common token icons are included others are used via external repositories inside `remotes/`. Feel free to update them frequently. Hint: they are heavy cached. See cache clear section
Init them once via:

```
git submodule update --init --recursive
```

## Run

For a running development system run the following command. You can also use nginx or Symfony encore (Webpack) stuff 

```
symfony server:start
npm run-script dev-server
bin/console d:s:u --force
```

```
http://127.0.0.1:8000
```

If you want need a running backend checkout also https://github.com/farm-army/farm-army-backend.
You can also change backend url via `BACKEND_BASE_URL` or running chain `CHAIN` inside via .env

## Cache

There are several caches. Clear cache via Symfony command or clear `var/cache/*` folders

## Technical Debt

 - Migrate more page content to Vue.js and 3.x

## Folder Structure

 - `src` PHP code
 - `assets` Vue.js "applications" provided in subdirectories
 - `remotes` External repositories that provide value for the project eg icons and token lists