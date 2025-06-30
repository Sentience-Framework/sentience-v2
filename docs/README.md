```
  ____             _   _                     
 / ___|  ___ _ __ | |_(_) ___ _ __   ___ ___ 
 \___ \ / _ \ '_ \| __| |/ _ \ '_ \ / __/ _ \
  ___| |  __/ | | | |_| |  __/ | | | |_|  __/
 |____/ \___|_| |_|\__|_|\___|_| |_|\___\___|

 The lightweight API framework

 By UniForceMusic                                             
```

Individual documentation of modules can be found in the `docs` folder.

Examples and starting points can be found in the `examples` folder.

# Quickstart guide

## 1. Requirements

Sentience has the following dependencies:
- PHP ^8.2
- Composer
- PDO mysql extension
- PDO pgsql extension

## 2. Generating autoload file

Run the following command:
```
composer install
```

## 3. Creating a .env

Create a new file called `.env` in the root of your project. Copy the contents of `.env.example` to the file.

## 4. Start the development server

Run the following command:
```
php sentience.php server:start
```

Open your browser to http://localhost:8000/healthcheck, and confirm Sentience is running as it should.
