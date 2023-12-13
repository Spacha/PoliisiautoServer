## Important notice!

**This is a prototype and must not be used in practice under any circumstances! The developers of this application do not take any responsibility, legal or otherwise, of using this application!**

---

# Poliisiauto server

![Logo of Poliisiauto](docs/logo-text-0.5x.png)

A server for an application where users can report bullying to a trusted adult.

See [this Google Drive document](https://docs.google.com/spreadsheets/d/1WYGZZfEpqy50AALHSY2IM9s3xUBot-i0YONzvU3Gz-4/edit#gid=1449701033) for initial server specification.

This server offers an API for various clients such as mobile applications (specifically for [PoliisiautoApp](https://github.com/Spacha/PoliisiautoApp)). The API has a public endpoint for authentication and a large set of protected endpoints for authenticated users.

See the complete **[API description here](https://documenter.getpostman.com/view/3550280/2s8YzUwMLQ#auth-info-5fd01ded-b632-4259-b02d-26f74ddd579e)**.

## Getting started

This software is build on Laravel 9 and requires a (PHP) web server capable of running it as well as a MySQL database. See the requirements in more detail in Laravel documentation: https://laravel.com/docs/9.x.

## Project initialization

Access your server using `ssh` or other means. Navigate to the folder where you want to install the project.

Clone the project:
```bash
$ git clone https://github.com/Spacha/PoliisiautoServer.git
```

Install the dependencies using `composer`.
```bash
$ cd PoliisiautoServer
$ composer install
```

Create a `.env` file by copying the `.env.example` (see [here](https://laravel.com/docs/9.x/configuration) for more information)
```bash
$ cp .env.example .env
```

Change necessary values in the `.env` files. Most important lines are these:
```yaml
DB_DATABASE=poliisiauto
DB_USERNAME=root
DB_PASSWORD=secret
```

These should obviously match your database configuration. You need to have a database with that name before it can be initialized.

Generate an application key:
```bash
$ php artisan key:generate
```

Migrate the database (see [here](https://laravel.com/docs/9.x/migrations) for more information):
```bash
$ php artisan migrate:fresh
```

**NOTE:** You may need to change the permissions of some folders (usually everything under `storage`).

```bash
$ chmod 0777 -R storage
```

### Running tests

The tests can be run by (see [here](https://laravel.com/docs/9.x/testing#running-tests) for more information):
```bash
$ php artisan test
```

### Database seeding

If you want, you can "seed" the database with sample users and organizations using:
```bash
$ php artisan db:seed
```

## License

PoliisiautoSERVER is licensed under a 2-clause BSD license. See [LICENSE](LICENSE) for more details.
