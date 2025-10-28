# API: Организации, Здания, Виды деятельности

REST API для поиска организаций с фильтрацией по:
- Зданиям
- Видам деятельности (с деревом и потомками)
- Геолокации (радиус, bbox)
- Названию

Использует **PostGIS**, **Closure Table**, **Eloquent**, **OpenAPI (Swagger)**.

---

## Стек

- PHP 8.2+
- Laravel 10
- PostgreSQL + PostGIS
- Docker Compose

---

## Установка

### 1. Клонирование

```bash
git clone <your-repo>
cd <project>

в etc/hosts прописать:
127.0.0.1       org-catalog.localhost

API_KEY = 2332
