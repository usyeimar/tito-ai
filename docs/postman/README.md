# Postman Collection Management

Esta carpeta contiene los archivos modulares de la colección Postman, divididos para facilitar el mantenimiento y trabajo colaborativo.

## Estructura

```
docs/postman/
├── collection.postman.json          # Archivo base con info y variables
├── tenant/                          # Endpoints de tenant
│   ├── _index.postman.json
│   ├── activity/
│   │   ├── _index.postman.json
│   │   └── get.list-activity.postman.json
│   ├── auth/
│   │   ├── _index.postman.json
│   │   └── post.login.postman.json
│   └── ...
└── central/                         # Endpoints centralizados (si aplica)
    └── ...
```

## Scripts

### Dividir colección (split)

Para dividir una colección grande en archivos modulares:

```bash
python3 scripts/split-postman.py \
    --input postman-collection.json \
    --output docs/postman \
    --report
```

**Parámetros:**

- `--input`: Archivo de colección grande (default: `postman-collection.json`)
- `--output`: Directorio de salida (default: `docs/postman`)
- `--report`: Muestra resumen de la operación

### Fusionar colección (merge)

Para fusionar los archivos modulares de vuelta a una colección:

```bash
python3 scripts/merge-postman.py \
    --source docs/postman \
    --base collection.postman.json \
    --output postman-collection.json \
    --mode tree \
    --report
```

**Parámetros:**

- `--source`: Directorio con archivos modulares (default: `docs/postman`)
- `--base`: Archivo base (default: `collection.postman.json`)
- `--output`: Archivo de salida (default: `postman.json`)
- `--mode`: Modo de fusión: `tree`, `legacy`, o `dual` (default: `tree`)
- `--strict`: Valida que el método del filename coincida con request.method
- `--report`: Muestra resumen de la operación

## Convenciones de Nombres

### Archivos de endpoint

- Formato: `{method}.{nombre-descriptivo}.postman.json`
- Ejemplos:
    - `get.list-users.postman.json`
    - `post.create-user.postman.json`
    - `patch.update-user.postman.json`
    - `delete.user.postman.json`

### Archivos de índice (carpetas)

- Nombre: `_index.postman.json`
- Contiene metadatos de la carpeta:
    ```json
    {
        "name": "Users",
        "order": 10,
        "requests_order": 10
    }
    ```

## Flujo de Trabajo

### Editar un endpoint existente

1. Localiza el archivo en la estructura de carpetas
2. Edita el archivo `.postman.json` correspondiente
3. Ejecuta el merge para generar la colección actualizada

### Agregar un nuevo endpoint

1. Crea el archivo en la carpeta apropiada siguiendo la convención de nombres
2. Ejecuta el merge para generar la colección actualizada

### Cambiar estructura de carpetas

1. Mueve los archivos de endpoint a las nuevas ubicaciones
2. Actualiza o crea los archivos `_index.postman.json` necesarios
3. Ejecuta el merge para generar la colección actualizada

## Estadísticas

La colección actual tiene:

- **124 endpoints** organizados en
- **41 carpetas** con
- **123 variables** de colección
