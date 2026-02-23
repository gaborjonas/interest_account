# Interest Account

## Available commands

#### Run demo in production mode
Build a production-ready container and run a demo in CLI.
```shell
make run-demo
```

#### Run container for development
Start Docker containers and install dependencies for development.

```shell
make dev-setup
```

#### Run all quality checks
Runs coding style check, static analysis, automated tests, and mutation tests.
```shell
make check
```

#### Install dependencies
Install Composer dependencies in the container.
```shell
make install
```

#### Open bash shell in a container
Opens bash shell inside the Docker container.
```shell
make shell
```

#### Clean environment
Remove vendor and temp directories
```shell
make clean
```

### Docker Management

#### Start Docker container
Start Docker containers in detached mode.
```shell
make docker-up
```

#### Stop Docker container
Stop and remove Docker containers, volumes, and orphans.
```shell
make docker-down
```

#### Fix code style issues
Automatically fix code style issues using PHP CS Fixer.
```shell
make lint
```

#### Check code style
Check code style without making changes.
```shell
make lint-check
```

#### Run static analysis
Run PHPStan static analysis to detect potential bugs.
```shell
make stan
```

#### Run tests
Run PHPUnit test suite.
```shell
make test
```

#### Run mutation tests
Run Infection mutation testing to assess test quality.
```shell
make mutation
```

