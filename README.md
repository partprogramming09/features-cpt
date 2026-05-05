# Features CPT

Plugin de WordPress para registrar y gestionar el Custom Post Type de caracteristicas, con soporte para:

- Meta box en admin para boton (URL, texto y colores)
- Campos en REST API para uso headless
- Campos en WPGraphQL (si el plugin esta activo)
- Compatibilidad con Polylang para contenido traducible

## Requisitos

- WordPress 6.x o superior
- PHP 7.4+ (recomendado 8.x)
- Opcional:
  - WPGraphQL
  - Polylang / Polylang Pro

## Instalacion

1. Copia la carpeta `features-cpt` dentro de `wp-content/plugins/`.
2. Ve a **Plugins** en WordPress.
3. Activa **Features CPT**.

## Que registra

- CPT: `caracteristica`
- Slug: `caracteristicas`
- Soporta: `title`, `editor`, `thumbnail`, `excerpt`
- Taxonomias: `category`, `post_tag`

## Campos de metadatos (internos)

- `_boton_url`
- `_url_saber_mas` (compatibilidad legado)
- `_boton_texto`
- `_boton_color_fondo`
- `_boton_color_texto`

## REST API (campos expuestos)

Sobre el tipo `caracteristica`:

- `tab_title`
- `button_url`
- `button_label`
- `button_bg_color`
- `button_text_color`
- `url_saber_mas` (alias legado)

Ejemplo de endpoint:

`/wp-json/wp/v2/caracteristica`

## WPGraphQL (si esta activo)

Sobre el tipo `Caracteristica`:

- `buttonUrl`
- `buttonLabel`
- `buttonBgColor`
- `buttonTextColor`

## Polylang

- Marca el CPT `caracteristica` como traducible.
- Si guardas una entrada sin idioma, asigna el idioma por defecto.

## Notas de compatibilidad

- El plugin mantiene `_url_saber_mas` para proyectos antiguos.
- Si no se define protocolo en la URL (ej: `dominio.com`), se guarda como `https://dominio.com`.

## Versionado recomendado

- Usa tags en GitHub: `v1.0.0`, `v1.1.0`, etc.
- Documenta cambios por version en el repositorio.
