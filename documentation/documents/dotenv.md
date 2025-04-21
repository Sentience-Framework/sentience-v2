# DotEnv

Just like most other frameworks, Sentience supports environment variables via `.env` files. Where Sentience takes it up a notch is type support.

The following types are supported:
- Null
- Booleans
- Integers
- Floats
- Strings
- Template strings
- Arrays

## 1. Syntax

### 1.1 Null
```
VAR_NULL=null
```

### 1.2 Booleans
```
VAR_TRUE=true
VAR_TRUE=false
```

### 1.3 Integers
```
VAR_INTEGER=1234
```

### 1.3 Integers
```
VAR_INTEGER=1234
```

### 1.4 Floats
```
VAR_FLOAT=12.34
```

### 1.5 Strings
```
VAR_STRING='this is a string'
```

### 1.6 Template strings
```
VAR_APP_NAME='Sentience'
VAR_TEMPLATE_STRING="${VAR_APP_NAME} supports Javascript like template strings"
```

### 1.6 Arrays
```
# Nested arrays are not supported

VAR_ARRAY=[null, true, 1, 2.3, '4', "${FIVE}"]
```

## 2. Variable access

### 2.1 Loading existing environment variables

Sentience loads the variables from the existing environment before loading the `.env` file. The `.env` file has access to all the existing environment variables.

### 2.2 Accessing variables

Sentience offers a function called env(). The first argument is the key name, the second argument is a fallback in case the key cannot be found.

## 3. Importing missing variables

Sentience offers a way to automatically import missing variables from the .env.example file.

```
php sentience.php dotenv:fix
```

This is mainly useful when you're working with multiple developers on the same project, and some of them forget to check for added .env variables.
