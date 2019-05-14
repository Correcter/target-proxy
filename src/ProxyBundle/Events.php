<?php

namespace ProxyBundle;

/**
 * @author Vitaly Dergunov
 */
class Events
{
    // Запос к проксе.
    const PROXY_REQUEST = 'proxy.request';

    // Проверим лимиты запросов на токен клиента
    const CHECK_REQUEST_LIMITS = 'check.request.limits';

    // Извлечем информацию о пользователе проксе или создадим нового с параметрами по-умолчанию
    const CREATE_USER_IF_NOT_EXISTS = 'create.user.if.not.exists';

    // Синхронизируем клиентов из target mail
    const UPDATE_AGENCY_CLIENTS = 'update.agency.clients';

    // Проверим соответствие компании и полученному токену от клиента
    const CHECK_COMPANY = 'check.company';

    // Проверим запрос на наличие смысла
    const CHECK_URI = 'check.uri';

    // Проверим запрос на наличие смысла
    const CHECK_PROXY_TYPE = 'check.proxy.type';

    // Выберем связку агентство-креденшинал
    const SETUP_CREDENTIALS = 'setup.credentials';

    // Проверим передали ли агентство
    const AGENCY_AND_CLIENT_IS_RECEIVED = 'agency.and.client.is.received';

    // Проверим доступны ли клиенту вызываемые HTTP методы
    const CHECK_TOKEN = 'check.token';

    // Проверим доступны ли клиенту вызываемые HTTP методы
    const CHECK_HTTP_METHOD = 'check.http.method';

    // Проверим можно ли пускать клиента, запросившего такой метод
    const CHECK_ACCESS_METHODS = 'check.access.methods';
}
