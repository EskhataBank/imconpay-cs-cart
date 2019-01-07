#Модуль оплаты для cs-cart 4.x
Модуль оплаты через платежный шлюз ImconPay по протоколу версии 1.0 https://pay.imcon.tj/docs
Совместим с версией CS-cart 4.5.1+ (4.4.х могут быть конфликты)

##Установка

###Автоматическая
1. Убедитесь в соответствии версий модуля и вашей CMS, они должны совпадать.
2. Перейти в админ панели в раздел "Модули - Управление модулями", нажать на кнопку установки нового модуля в правом верхнем углу
3. Выбрать архивный файл модуля на компьютере, нажать "Установить". (права на папки app/addons, app/payments должны быть 777)
4. В случае успешной установки, активировать модуль. (Он появится в списке доступных модулей)

###Ручная

1. Убедитесь в соответствии версий модуля и вашей CMS, они должны совпадать.
2. Распаковать архив с модулем
3. Скопировать вручную файлы на сервер в корневую папку
4. Найти модуль в списке доступных модулей, активировать его.

##Настройка


1. Перейти в раздел "Администрирование - Способы оплаты", нажать добавить способ оплаты ("+")
2. Название модуля "ImconPay", выбрать процессор из списка "ImconPay", Категория оплаты "Интернет платежи"
3. После выбора станет доступна закладка Настроек модуля.
4. Копируем из аккаунта ImconPay код вашего клиента, секретный ключ (api key),внутренный секретный ключ (root api key) также можно использовать тестовый ключ для проверки работы системы.
5. Сохраняем, модуль готов к работе
