# Hostaway phone book API project
- API documentation [http://localhost/api/documentation](http://localhost/api/documentation)
![Documentation](public/images/docs.png?raw=true "Documentation")

- Example query [http://localhost/api/items](http://localhost/api/items?api_token=24f56647eddc650bd0904883dd7168e609017696cf69714fe7d1224012491710)
![Request](public/images/request.png?raw=true "Request")

- Test results
![Test](public/images/test.png?raw=true "Test")

### Features
- API pagination with metadata
- API auth token 
- Logging all API queries to 'api.log', errors to 'error.log' 
- Caching external API calls
- Docker support, Laravel sail
- Open API [documentation](http://localhost/api/documentation)
- GitHub Actions - auto testing on push
- Validation by value type, DB query, API calls
- Middleware logging and auth checking

### Development installation instructions

#### Before start (Windows 10)

1. Enable windows subsystem for linux [wsl2 install instructions](https://docs.microsoft.com/ru-ru/windows/wsl/install-win10#step-1---enable-the-windows-subsystem-for-linux)
2. Install [Docker Desktop](https://www.docker.com/products/docker-desktop) (docker install [instructions](https://docs.docker.com/docker-for-windows/wsl/)).

#### Installation

##### 1. Clone repository

```sh
$ git clone https://github.com/GeorgII-web/hostaway-phonebook.git hostaway-phonebook
$ cd hostaway-phonebook
```

##### 2. Copy .env.example to .env
```sh
$ cp .env.example .env
```

##### 3. Docker (php 8.0) "composer install"
Download *vendor* folder.
```sh
$ docker run --rm \
    -v $(pwd):/opt \
    -w /opt \
    laravelsail/php80-composer:latest \
    composer install
```

##### 4. Sail start
- Download docker images for laravel/mysql/redis...
```sh
$ ./vendor/bin/sail up
```
- Sail [instructions](https://laravel.com/docs/8.x/sail#executing-sail-commands)

##### 5. Generate new app key
```sh
$ sail artisan key:generate
```

##### 6. Migrate DB scheme
```sh
$ sail artisan migrate
```

##### 7. DB seed
```sh
$ sail artisan db:seed
```

##### 8. Post install commands
```sh
$ sail artisan optimize
```

##### 8. Test
```sh
$ sail test
```

##### 9. Generate API documentation
```sh
$ sail artisan l5-swagger:generate
```
