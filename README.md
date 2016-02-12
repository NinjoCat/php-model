# Пример использования

Требуется операционная система linux и установленные php, mysql и composer.

Для запуска демо приложения нужно выполнить следующие команды:

(в команду выполнения sql-скрипта нужно подставить ваш пароль mysql пользователя root, и этот же пароль надо будет указать в конфиге PHPModelTest/Config.php)

    git clone https://github.com/o-log/php-model.git
    cd php-model
    composer install
    mysql -u root -p1 < PHPModelTest/db_init.sql
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
        use \OLOG\Model\ActiveRecord;
        use \OLOG\Model\ProtectProperties;

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

# Возможности

## ActiveRecord

Этот трейт помогает загружать и сохранять простые объекты в БД.

При загрузке (метод load()) он читает запись из таблицы БД и записывает каждое поле записи в поле объекта с соответствующим именем.

При записи (метод save()):

- если у объекта есть непустое значение поля id - обновляет в таблице БД запись с таким id. в каждое поле записи заносится значение из поля объекта с соответствующим именем.

- если у объекта пустое значение id - создает в таблице БД новую запись, получает ее id и записывает его в объект. в каждое поле записи заносится значение из поля объекта с соответствующим именем.

Для того, чтобы класс можно было использовать с activerecord, он должен:

- иметь поле id (реализовывать интерфейс interfaceLoad)

- поля объекта должны совпадать с полями таблицы в бд (в т.ч имена)

- иметь константы DB_ID и DB_TABLE_NAME

### игнорирование полей при изменении структуры таблицы БД

...

## Factory

Трейт позволяет сразу создать объект и загрузить его данные из таблицы БД по идентификатору.

Пример использования:

    $model_obj = \PHPModelTest\TestModel::factory($model_id);

Если записи с таким идентификатором в таблице нет - будет выброшено исключение.

Также фабрика предоставляет функционал сброса кэша модели при ее изменении или удалении.

## ProtectProperties

Сервисный трейт, выбрасывает исключения при обращении к необъявленным в классе свойствам объекта.

Можно использовать при желании, можно не использовать.

