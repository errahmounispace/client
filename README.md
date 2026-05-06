# Laraowl Client for Laravel

Laraowl is a self-hosted monitoring solution for Laravel applications. This package allows you to collect real-time metrics, exceptions, and performance data from your applications and send them to your Laraowl Server.

## Features

- **Exception Tracking**: Detailed reports on errors with stack traces and request data.
- **Performance Monitoring**: Track database queries, jobs, and execution times.
- **Service Integration**: Built-in support for Mail, Notifications, and Cache monitoring.
- **Zero Configuration**: Sensible defaults that work out of the box.

## Installation

Install the package via Composer:

```bash
composer require laraowl/client
```

## Configuration

### 1. Run the Install Command

Laraowl provides a convenient installation command to set up the configuration and environment variables:

```bash
php artisan laraowl:install
```

This command will:
- Publish the `laraowl.php` configuration file.
- Prompt you for your Laraowl Server URL and Project Token.
- Automatically update your `.env` file.


### 2. Environment Setup

Add your Laraowl Server details to your `.env` file:

```env
LARAOWL_SERVER_URL=https://your-laraowl-server.com
LARAOWL_TOKEN=your-project-token
```

## License

Laraowl Client is open-source software licensed under the [MIT license](LICENSE.md).

