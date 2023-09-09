# Laravel CRUD Generator
Laravel CRUD Generator


# Usage

## Generate CRUD Model + View + Controller + Migration + Request

```
php artisan crud:generate --help
Usage:
  crud:generate <name> [options] [--]

Arguments:
  name

Options:
      --attributes= : Enter filelds with type like title:strin,description:text

Example:
      php artisan crud:generate Post --attributes=title:string,discription:text,status:boolean

Valid types are:
      'string','text','boolean','decimal','float','integer'
```

