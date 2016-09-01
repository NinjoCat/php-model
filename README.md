Библиотека может использоваться как автономно, так и совместно с:

- Роутер: https://github.com/o-log/php-router

- Работа с объектами: https://github.com/o-log/php-model

- Компоненты админки: https://github.com/o-log/php-crud

- Адаптер bootstrap: https://github.com/o-log/php-bt

- Генератор каркаса приложений: https://github.com/o-log/create_application

# Общие характеристики

В качестве автозагрузчика классов и менеджера зависимостей используется composer.

Библиотека соответствует следующим соглашениям:

http://www.php-fig.org/psr/psr-0/

http://www.php-fig.org/psr/psr-1/

http://www.php-fig.org/psr/psr-2/

http://www.php-fig.org/psr/psr-4/

Педдерживаемые СУБД: MySQL, Postgres.

Кэш: memcached.

# Основные возможности

Трейт для загрузки объектов из БД по идентификатору с многоуровневым кэшированием (Factory).

Трейты для загрузки и сохранения объектов в БД и их удаления по идентификатору (ActiveRecordTrait). Содержит инфраструктуру для отслеживания изменения объектов и обновления кэша. 

Библиотека для выполнения sql-запросов.
 
Библиотека для работы с memcached.

Утилита для создания php-класса для новых объектов и структуры БД для него, сразу подключает нужные для работы трейты.
 
Утилита для добавления новых свойств к объектам и соответствующего изменения структуры БД, включая создание вторичных ключей и методов для выборки списков.

Утилита миграции БД для переноса изменений в структуре БД между инстансами приложения.

# Пример использования

Требуется операционная система linux и установленные php, mysql и composer.

Для запуска демо приложения нужно выполнить следующие команды:

(в команду выполнения sql-скрипта нужно подставить ваш пароль mysql пользователя root, и этот же пароль надо будет указать в конфиге PHPModelTest/Config.php)

    git clone https://github.com/o-log/php-model.git
    cd php-model
    composer install
    php5 cli.php
    cd public
    php5 -S localhost:8000

После этого можно открыть в браузере адрес localhost:8000

Код создания новой модели и сохранения ее в БД выглядит примерно так:

    $new_model = new \PHPModelTest\TestModel();
    $new_model->setTitle(rand(1, 1000));
    $new_model->save();

Вот пример загрузки модели из БД с использованием фабричного метода:

    $model_obj = \PHPModelTest\TestModel::factory($model_id);
    
Как выглядит класс модели:
    
    class TestModel implements \OLOG\Model\InterfaceFactory
    {
        use \OLOG\Model\FactoryTrait;
        use \OLOG\Model\ActiveRecordTrait;
        use \OLOG\Model\ProtectPropertiesTrait;
        
        const DB_ID = \PHPModelTest\Config::DB_NAME_PHPMODELTEST;
        const DB_TABLE_NAME = 'test_model';
        
        protected $id = 0;
        protected $title = '';
        
        public function getTitle(){
            return $this->title;
        }
        
        public function setTitle($title){
            $this->title = $title;
        }
    }    

# Подключение настроек

Чтобы библиотека могла получить доступ к базе данных, перед ее использованием нужно указать настройки:

    \PHPModelDemo\Config::init();
    
Это можно сделать например в начале точки входа (файла index.php).
    
Пример конфигурации:

    class Config
    {
        const DB_NAME_PHPMODELDEMO = 'phpmodel';
    
        public static function init()
        {
            DBConfig::setDBSettingsObj(
                self::DB_NAME_PHPMODELDEMO,
                new DBSettings('localhost', 'phpmodel', 'root', '1')
            );
        }
    }

# ActiveRecordTrait

Этот трейт помогает загружать и сохранять простые объекты в БД.

При загрузке (метод load()) он читает запись из таблицы БД и записывает каждое поле записи в поле объекта с соответствующим именем.

При записи (метод save()):

- если у объекта есть непустое значение поля id - обновляет в таблице БД запись с таким id. в каждое поле записи заносится значение из поля объекта с соответствующим именем.

- если у объекта пустое значение id - создает в таблице БД новую запись, получает ее id и записывает его в объект. в каждое поле записи заносится значение из поля объекта с соответствующим именем.

Для того, чтобы класс можно было использовать с activerecord, он должен:

- иметь поле id (реализовывать интерфейс interfaceLoad)

- поля объекта должны совпадать с полями таблицы в бд (в т.ч имена)

- иметь константы DB_ID и DB_TABLE_NAME

## Переопределение и дополнение методов загрузки, сохранения и удаления
  
Методы load, save и delete, которые предоставляются трейтом, можно переопределить в классе модели и включить в них собственную логику.

При необходимости дополнительной обработки перед сохранением или после сохранения модели можно определить методы beforeSave() и afterSave():

    public function beforeSave(){
        $this->setBody($this->getTitle() . $this->getTitle());
    }

    /**
     * overrides factoryTrait method
     */
    public function afterSave()
    {
        $term_to_node_ids_arr = DemoTermToNode::getIdsArrForNodeIdByCreatedAtDesc($this->getId());
        foreach ($term_to_node_ids_arr as $term_to_node_id){
            $term_to_node_obj = DemoTermToNode::factory($term_to_node_id);
            $term_to_node_obj->setCreatedAtTs($this->getCreatedAtTs());
            $term_to_node_obj->save();
        }
        
        $this->removeFromFactoryCache();
    }

Метод beforeSave() позволяет изменить данные модели перед сохранением или заблокировать сохранение.
Метод afterSave() позволяет например сбросить кэш модели или обновить данные связанных моделей.

Аналогичная пара методов вызывается при удалении модели: canDelete() и afterDelete():

    public function canDelete(&$message){
        if ($this->getDisableDele!te()){
            $message = 'Delete disabled';
            return false;
        }

        return true;
    }

    public function afterDelete(){
        $match_obj = Match::factory($this->getMatchId());

        // обновляем тайтл матча после отвязывания команд
        $match_obj->regenerateTitle();
        $match_obj->save();
    }

Методы afterSave() и afterDelete() имеют умолчательную реализацию, которая сбрасывает кэш фабрики для модели. При переопределении этих методов нужно не забывать включать в них сброс кэша фабрики.

## Транзакции

Сохранение и удаление модели выполняются в рамках транзакции, поэтому можно безопасно выбрасывать исключение внутри beforeSave(), afterSave(), canDelete() и afterDelete() - при этом база вернется к состоянию до начала сохранения или удаления.

## Игнорирование полей при изменении структуры таблицы БД

...

## Хранение идентификатора в поле с именем, отличным от id

...

# Factory

Трейт позволяет сразу создать объект и загрузить его данные из таблицы БД по идентификатору.

Пример использования:

    $model_obj = \PHPModelTest\TestModel::factory($model_id);

Если записи с таким идентификатором в таблице нет - будет выброшено исключение.

Также фабрика предоставляет функционал сброса кэша модели при ее изменении или удалении.

# Контроль ссылочной целостности данных

Для контроля ссылочной целостности рекомендуется создавать вторичные ключи в БД для всех полей, которые ссылаются на модели в других таблицах.

Вторичные ключи можно добавить утилитой добавления свойств к моделям непосредственно при создании свойства.

Если ссылающееся поле может быть пустым - оно должно быть создано как nullable без значения по умолчанию. При этом отсутствие ссылки будет сохраняться как null. 

# Библиотека для работы с БД

Работа с БД не требует явного подключения БД - соединение устанавливается при выполнении первого запроса. Это позволяет в большинстве случаев генерировать страницы вообще не устанавливая соединение с БД (если все данные приходят из кэша).

Для выполнения запросов предназначен класс DBWrapper. Основные методы:

    static public function readColumn($db_id, $query, $params_arr = array())
    
Метод принимает идентификатор БД из конфига, строку запроса и массив значений параметров (подстановку значений параметров выполняет PDO).

Возвращается массив значений какого-то одного поля для выбранных запросом записей.

Этот метод используется например для выборки списков идентификаторов объектов. 

## Нстройка подключения к БД

Пример конфигурации для одной базы данных:

    <?php
    
    namespace PHPModelDemo;
    
    class Config
    {
        const DB_NAME_PHPMODELDEMO = 'phpmodel';
    
        public static function init()
        {
            DBConfig::setDBSettingsObj(
                self::DB_NAME_PHPMODELDEMO,
                new DBSettings('localhost', 'phpmodel', 'root', '1')
            );
        }
    }

Эта конфигурация должна подключаться в точках входа такой командой:

    \PHPModelDemo\Config::init();

## Транзакции

...

## Работа с БД модулей

Если приложение использует несколько модулей - оно должно иметь возможность выполнять sql-запросы от каждого из модулей, для этого в конфиге приложения нужно указать отдельно БД для каждого модуля. Вот пример:

    public static function init()
    {
        DBConfig::setDBSettingsObj(
            \OLOG\Auth\Constants::DB_NAME_PHPAUTH,
            new DBSettings('localhost', 'db_app', 'root', '1', 'vendor/o-log/php-auth/db_phpauth.sql')
        );

        DBConfig::setDBSettingsObj(
            self::DB_NAME_PHPMODELDEMO,
            new DBSettings('localhost', 'db_app', 'root', '1')
        );
    }

Здесь база приложения называется db_app, при этом приложение использует модуль php-auth, БД для которого прописывается в конфиге отдельно, для того, чтобы подключить дополнительный файл sql-запросов. Мигратор запросов будет обрабатывать все БД из конфига, таким образом если в модуле php-auth появятся изменения структуры БД - мигратор их увидит.

БД для модуля php-auth в конфиге стоит раньше, чем основная БД приложения, потому что таблицы приложения могут ссылаться на таблицы модулей, и поэтому таблицы модулей нужно создать до таблиц приложения. Мигратор обрабатывает БД в том порядке, в котором они идут в конфиге.

При этом все БД в конфиге могут на самом деле смотреть на одну физическую БД - таким образом можно настраивать ссылочную целостность и т.п.

# Библиотека для работы с кэшом

Конфигурация для работы с мемкэшом:
 
    CacheConfig::addServerSettingsObj(
        new MemcacheServerSettings('localhost', 11211)
    );
    
Основные методы для работы с кэшом находятся в классе OLOG\Cache\CacheWrapper:

    static public function set($key, $value, $expire = -1)
    static public function get($key)
    static public function delete($key)
    static public function increment($key)

Если с одним сервером мемкеша работает несколько сайтов - нужно добавить уникальный префикс ключа кэша:

    CacheConfig::setCacheKeyPrefix('site_instance_uniq_name');

# ProtectPropertiesTrait

Сервисный трейт, выбрасывает исключения при обращении к необъявленным в классе свойствам объекта.

Можно использовать при желании, можно не использовать.

# Миграция структуры БД

Утилита решает задачу переноса изменений в структуре БД между разными инстансами приложения: от одного разработчика к другим, на продакшен и т.п.
Чтобы выполнить на текущем инстансе новые запросы нужно запустить утилиту вручную: выполнить cli.php в корне приложения и выбрать в меню "Выполнение SQL-запросов".

Все sql-запросы, изменяющие структруру БД, сохраняются в файлах в корневой папке приложения: по одному файлу на каждую БД, используемую в приложении. Имя файла соответствует ключу конфига для данной БД.

Запросы заносятся в файл в том порядке, в котором они должны выполняться, в виде экмпортированного php-массива.
Запросы можут быть добавлены как утилитами создания и изменения моделей, так и руками напрямую в файл.

Массив запросов не содержит индексы, потому что индексы генерируются локально и могут конфликтовать если несколько разработчиков добавляют запросы одновременно в разных инстансах.

Для того, чтобы каждый запрос выполнялся на конкретном инстансе только один раз, утилита сохраняет список выполненных на данном инстансе запросов в специальной таблице в БД. Эта таблица создается при первом запуске утилиты.

В таблице сохраняется непосредственно текст запроса. Т.е. если добавить в файл еще раз запрос, который уже выполнялся - он будет проигнорирован. Это может быть проблемой если например разработчик добавил в таблицу поле, через некоторое время удалил его, а потом решил снова добавить: при этом в таблице появится второй запрос на создание поля, идентичный первому, и его нужно выполнить.
Для решения этой проблемы утилиты генерации и изменения моделей добавляют в sql-запросы случайную часть, которая делает строку запроса уникальной (в виде комментария). При добавлении запросов руками рекомендуется придерживаться этого же подхода.

# Создание новых моделей

Чтобы создать новую модель нужно выполнить cli.php в корне приложения и выбрать в меню "Создание модели".

Утилита запросит нужные данные и создаст новую модель, включая:
- php-класс модели
- структуру таблицы в БД
- метод выборки списка всех объектов модели

# Добавление новых полей к модели

Чтобы создать поле к модели нужно выполнить cli.php в корне приложения и выбрать в меню "Добавление поля модели".
Утилита запросит нужные данные и внесет изменения в класс модели, сгенерирует sql-скрипт обновления таблицы БД и если нужно - создаст метод выборки объектов по новому полю.

# Выбор списка моделей по значению поля

Автоматически создаваемые методы выборки объектов по значению поля FIELD называются getIdsArrForFIELDByCreatedDesc().

Как расшифровывается это название метода:

- getIdsArr - вернуть массив идентификаторов
- ForFIELD - для указанного значения поля FIELD
- ByCreatedDesc - с сортировкой по убыванию поля created_at_ts

# Методология

Библиотека сложилась в процессе разработки нескольких крупных порталов и решает следующие задачи:

- минимальный порог вхождения разработчиков
- упрощение и ускорение разработки и поддержки кода
- максимальное быстродействие: минимум накладных расходов и простота оптимизации

Основные подходы, реализованные в библиотеке:

- минимизируем связанность кода, т.е. уменьшаем срок жизни переменных и количество данных, передаваемых внутри кода:
    - между компонентами передаем только минимально необходимые данные
    - переменные (в т.ч. объекты) инициализируем непосредственно перед использованием. Если объект используется много раз - получаем его из фабрики много раз. Многоуровневое кэширование в фабрике делает операцию получения объекта дешевой.
    - вместо объектов всегда передаем их идентификаторы
    - контекст не передаем во внутренние функции/шаблоны, а получаем его прямо там из провайдеров (контроллеров и т.п.)
- все операции выполняются явно

# Тесты

Скрипт test.sh в корневой папке запускает тесты.

# Использование WeightTrait

1. Создать у модели поле weight int not null default 0

2. В модели:

    implements WeightInterface
    
    use WeightTrait
    
3. В beforeSave() модели вызвать initWeight() с контекстом

После этого можно вывести в списке моделей widgetWeight, также передавая ему контекст. При этом таблицу нужно отсортировать по полю weight по возрастанию.