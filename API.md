# API

- Работа с пользователям
  - Создание пользователя
  - Редактирование данных пользователя
  - Удаление пользователя
  - Получение списка пользователей
  - Удаление пользователя
- Работа с пользовательскими ролями
  - Получение ролей пользователя
  - Добавление новой роли пользователю
  - Удаление роли у пользователя
- Работа с ролями
  - Создание роли
  - Редактирование роли
  - Удаление роли
  - Получение информации о роли
  - Получение списка ролей
- Привязка пермишнов к роли
  - Получение списка пермишнов, привязанных к роли
  - Привязка нового пермишна к роли
  - Отвязка пермишна от роли
- Работа с пермишнами
  - Получение информации о пермишне
  - Получение списка всех существующих пермишнов
- Аутентификация

Работа с пользователями
-----------------------

#### Создание пользователя:

*POST /api/user/*

параметры:
- username - уникальное имя пользователя
- email - уникальный e-mail пользователя
- password - не закодированный пароль пользователя 
- lastname - фамилия (не обязательно)
- name - имя (не обязательно)
- patronymic - отчество (не обязательно)
- workplace - место работы (не обязательно)
- duty - должность (не обязательно)
- bitrhdate - дата рождения в строковом формате (не обязательно)
- phone - номер телефона (не обязательно)

**Пример ответа (возвращается id созданного пользователя):**

```json
{
	"result": "ok",
	"id": "b24cbf72-e458-4c5d-a64e-02355814f6df"
}
```

#### Редактирование данных пользователя:

*PUT /api/user/{userId}/*

параметры:
- lastname - фамилия (не обязательно)
- name - имя (не обязательно)
- patronymic - отчество (не обязательно)
- workplace - место работы (не обязательно)
- duty - должность (не обязательно)
- bitrhdate - дата рождения в строковом формате (не обязательно)
- phone - номер телефона (не обязательно)

**Ответ в случае успешного сохранения:**

```json
{
  "result": "ok"
}
```

#### Удаление пользователя:

*DELETE /api/user/{userId}/*

**Ответ в случае успешного удаления:**

```json
{
  "result": "ok"
}
```

#### Получение информации о пользователе:

*GET /api/user/{userId}/*

**Пример ответа:**

```json
{
  "id": "b916f6cf-335c-4335-a13a-93f2737e2547",
  "username": "bsa",
  "email": "b-s-a@mail.ru",
  "is_active": true,
  "created_at": null,
  "updated_at": "2022-06-18 19:25:41",
  "lastname": "Бородинов",
  "name": "Сергей",
  "patronymic": "Александрович",
  "workplace": null,
  "duty": "программист",
  "phone": "+79851231078"
}
```

#### Получение списка пользователей:

*GET /api/user/*

параметры:
- limit (не обязательно) - количество результатов на странице (по умолчанию 10)
- page (не обязательно) - номер страницы

**Пример ответа (json-массив со списком пользователей):**

```json
[
  {
    "id": "b916f6cf-335c-4335-a13a-93f2737e2547",
    "username": "bsa",
    "email": "b-s-a@mail.ru",
    "is_active": true,
    "created_at": null,
    "updated_at": "2022-06-18 19:25:41",
    "lastname": "Бородинов",
    "name": "Сергей",
    "patronymic": "Александрович",
    "workplace": null,
    "duty": "программист",
    "phone": "+79851231078"
  },
  {
    "id": "b8a51948-ff27-4cb6-bd7c-6ef5955f4cbf",
    "username": "superadmin",
    "email": "super@admin.ru",
    "is_active": true,
    "created_at": "2022-06-18 23:23:56",
    "updated_at": "2022-06-18 23:23:56",
    "lastname": null,
    "name": null,
    "patronymic": null,
    "workplace": null,
    "duty": null,
    "phone": null
  },  
  ...
]
```

Работа с пользовательскими ролями
---------------------------------

#### Получить список ролей пользователя:

*GET /api/user/{userId}/role/*

**Пример ответа:**

```json
[
  {
    "id": "80353b84-7f78-4957-9ced-bedb0079ccd4",
    "name": "ROLE_SUPER_ADMIN",
    "title": "Супер администратор",
    "description": null,
    "created_at": "2022-06-18 23:23:56",
    "updated_at": "2022-06-18 23:23:56"
  },
  {
    "id": "80353b84-7f78-4957-9ced-bedb0079ccd4",
    "name": "ROLE_EDITOR",
    "title": "Редактор",
    "description": null,
    "created_at": "2022-06-18 23:23:56",
    "updated_at": "2022-06-18 23:23:56"
  },
  ...
]
```

#### Привязать пользователю новую роль:

*POST /api/user/{userId}/role/{roleId}*<br>
*POST /api/user/{userId}/role/[roleId, roleId, roleId]*

**Примеры запросов:**

POST /api/user/b8a51948-ff27-4cb6-bd7c-6ef5955f4cbf/role/5d75b01f-fbc5-4de0-9473-be484d829b47/
<br>(привязать одну роль)

POST /api/user/b8a51948-ff27-4cb6-bd7c-6ef5955f4cbf/role/[5d75b01f-fbc5-4de0-9473-be484d829b47,256ba933-e69d-45d5-9cb1-3540d367b322]/
<br>(привязать несколько ролей в пакетном режиме)

**Ответ в случае успешного выполнения:**

```json
{
	"result": "ok"
}
```

*Если у пользователя такая роль уже есть, ошибки не будет.*

#### Убрать роль у пользователя:

*DELETE /api/user/{userId}/role/{roleId}*<br>
*DELETE /api/user/{userId}/role/[roleId, roleId, roleId]*

**Примеры запросов:**

DELETE /api/user/b8a51948-ff27-4cb6-bd7c-6ef5955f4cbf/role/5d75b01f-fbc5-4de0-9473-be484d829b47/
<br>(удалить одну роль)

DELETE /api/user/b8a51948-ff27-4cb6-bd7c-6ef5955f4cbf/role/[5d75b01f-fbc5-4de0-9473-be484d829b47,256ba933-e69d-45d5-9cb1-3540d367b322]/
<br>(удалить несколько ролей в пакетном режиме)

**Ответ в случае успешного выполнения:**

```json
{
	"result": "ok"
}
```

*Если у пользователя такой роли уже нет, ошибки не будет.*

Работа с ролями
---------------

#### Создание роли:

*POST /api/role/*

параметры:
- name - машинное имя роли, например, 'ROLE_EDITOR'
- title - название на русском языке (не обязательно)
- description - описание (не обязательно)

**Пример ответа (возвращается id созданной роли):**

```json
{
	"result": "ok",
	"id": "b24cbf72-e458-4c5d-a64e-02355814f6df"
}
```

#### Редактирование роли:

*PUT /api/role/{roleId}/*

параметры:
- name - машинное имя роли
- title - название на русском языке
- description - описание

**Ответ в случае успешного сохранения:**

```json
{
  "result": "ok"
}
```

#### Удаление роли:

*DELETE /api/role/{roleId}/*

**Ответ в случае успешного удаления:**

```json
{
  "result": "ok"
}
```

#### Получение информации о роли:

*GET /api/role/{roleId}/*

**Пример ответа:**

```json
{
  "id": "80353b84-7f78-4957-9ced-bedb0079ccd4",
  "name": "ROLE_SUPER_ADMIN",
  "title": "Супер администратор",
  "description": null,
  "created_at": "2022-06-18 23:23:56",
  "updated_at": "2022-06-18 23:23:56"
}
```

#### Получение списка ролей:

*GET /api/role/*

**Пример ответа (массив json):**

```json
[
  {
    "id": "80353b84-7f78-4957-9ced-bedb0079ccd4",
    "name": "ROLE_SUPER_ADMIN",
    "title": "Супер администратор",
    "description": null,
    "created_at": "2022-06-18 23:23:56",
    "updated_at": "2022-06-18 23:23:56"
  },
  {
    "id": "80353b84-7f78-4957-9ced-bedb0079ccd4",
    "name": "ROLE_EDITOR",
    "title": "Редактор",
    "description": null,
    "created_at": "2022-06-18 23:23:56",
    "updated_at": "2022-06-18 23:23:56"
  },
  ...
]
```

Привязка пермишнов к роли
------------------------

#### Получить список пермишнов, привязанных к роли:

*GET /api/role/{roleId}/permission/*

**Пример ответа:**

```json
[
  {
    "id": "427935a0-c009-4f79-88f4-e50985998b60",
    "name": "can view users",
    "title": "может смотреть данные пользователей",
    "description": null,
    "created_at": "2022-06-18 23:23:56",
    "updated_at": "2022-06-18 23:23:56"
  },
  {
    "id": "427935a0-c009-4f79-88f4-e50985998b61",
    "name": "can edit users",
    "title": "может редактировать данные пользователей",
    "description": null,
    "created_at": "2022-06-18 23:23:56",
    "updated_at": "2022-06-18 23:23:56"
  },
  ...
]
```

#### Привязать новый пермишн к роли:

*POST /api/role/{roleId}/permission/{permissionId}*<br>
*POST /api/role/{roleId}/permission/[permissionId, permissionId, permissionId]*

**Примеры запросов:**

POST /api/role/b8a51948-ff27-4cb6-bd7c-6ef5955f4cbf/permission/5d75b01f-fbc5-4de0-9473-be484d829b47/
<br>(привязать один пермишн)

POST /api/role/b8a51948-ff27-4cb6-bd7c-6ef5955f4cbf/permission/[5d75b01f-fbc5-4de0-9473-be484d829b47,256ba933-e69d-45d5-9cb1-3540d367b322]/
<br>(привязать несколько пермишнов в пакетном режиме)

**Ответ в случае успешного выполнения:**

```json
{
	"result": "ok"
}
```

#### Отвязать пермишн от роли:

*DELETE /api/role/{roleId}/permission/{permissionId}*<br>
*DELETE /api/role/{roleId}/permission/[permissionId, permissionId, permissionId]*

**Примеры запросов:**

DELETE /api/role/b8a51948-ff27-4cb6-bd7c-6ef5955f4cbf/permission/5d75b01f-fbc5-4de0-9473-be484d829b47/
<br>(удалить один пермишн)

DELETE /api/role/b8a51948-ff27-4cb6-bd7c-6ef5955f4cbf/permission/[5d75b01f-fbc5-4de0-9473-be484d829b47,256ba933-e69d-45d5-9cb1-3540d367b322]/
<br>(удалить несколько пермишнов в пакетном режиме)

**Ответ в случае успешного выполнения:**

```json
{
	"result": "ok"
}
```

*Можно использовать в пакетном режиме - вместо id пермишна перезавать json-массив id пермишнов.*

Работа с пермишнами
-------------------

#### Получение информации о пермишне:

*GET /api/permission/{permissionId}/*

**Пример ответа:**

```json
{
  "id": "427935a0-c009-4f79-88f4-e50985998b60",
  "name": "can view users",
  "title": "может смотреть данные пользователей",
  "description": null,
  "created_at": "2022-06-18 23:23:56",
  "updated_at": "2022-06-18 23:23:56"
}
```

#### Получение списка всех существующих пермишнов:

*GET /api/permission/*

**Пример ответа (массив json):**

```json
[
  {
    "id": "427935a0-c009-4f79-88f4-e50985998b60",
    "name": "can view users",
    "title": "может смотреть данные пользователей",
    "description": null,
    "created_at": "2022-06-18 23:23:56",
    "updated_at": "2022-06-18 23:23:56"
  },
  {
    "id": "427935a0-c009-4f79-88f4-e50985998b61",
    "name": "can edit users",
    "title": "может редактировать данные пользователей",
    "description": null,
    "created_at": "2022-06-18 23:23:56",
    "updated_at": "2022-06-18 23:23:56"
  },
  ...
]
```

Аутентификация
--------------

#### Логин и получение токена:

*POST /api/login_check/*

header:Content-Type: application/json

json:
```json
{
	"username": {username},
	"password": {password}
}

```

**Пример ответа (возвращается JWT):**

```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE2NTY0MTM5MzAsImV4cCI6MTY1NjQxNzUzMCwicm9sZXMiOlsiUk9MRV9VU0VSIl0sInVzZXJuYW1lIjoiYnNhIn0.xf_1fRP6N5ULXNy68tlGWRAzvrnMqlZnn8w-owi3Alvx7lYIb69Zuw_GE7t1fq0kiizd1dZxTzvkMZAzCd43b6a_-irdXjz7CIHBdzg-3CYR9ciePZJX0b2XlJwfX1ZTqP40qGEbFvl6TXDJ_U4UhZr1iuWnN-8jWqtjG1CxcRPKr71gfdvV9J5Ky9DtqgdppNNyzvQpxIpOny7aXT4kutofD7s1u_pNPfYav4hto0Z6CWLYmxslm5qJGKiJe0lfeDiGCIMUX2UNJJ7kEfdVSqbEv4durx-APkVG_ZQDfA8yWUCJWDsMGKrrGSNrDPRbM67iw6a5-ETZ2OPelEnhlA"
}
```

**Пример ошибки:**

```json
{
  "code": 401,
  "message": "Недействительные аутентификационные данные."
}
```