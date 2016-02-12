## ProtectProperties

Сервисный трейт, выбрасывает исключения при обращении к необъявленным в классе свойствам объекта.

Можно использовать при желании, можно не использовать.

## ActiveRecord

Этот трейт помогает загружать и сохранять простые объекты в БД.

При загрузке (метод load()) он читает запись из таблицы БД и записывает каждое поле записи в поле объекта с соответствующим именем.

При записи (метод save()):

- если у объекта есть непустое значение поля id - обновляет в таблице БД запись с таким id. в каждое поле записи заносится значение из поля объекта с соответствующим именем.

- если у объекта пустое значение id - создает в таблице БД новую запись, получает ее id и записывает его в объект. в каждое поле записи заносится значение из поля объекта с соответствующим именем.

Для того, чтобы класс можно было использовать с activerecord, он должен:

- иметь поле id (реализовывать интерфейс interfaceLoad)

- поля объекта должны совпадать с полями таблицы в бд (в т.ч имена)

игнорирование полей при изменении структуры таблицы БД: ...