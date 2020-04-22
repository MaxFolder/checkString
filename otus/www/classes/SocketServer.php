<?php

namespace Classes;


class SocketServer {


    private $domainServerSocketFilePath;
    private $maxByteForRead;
    private $protocolFamilyForSocket;
    private $typeOfDataExchange;
    private $protocol;
    private $logger;

    public function __construct(ServerSocketDataBuilder $builder, LogInterface $logger) {

        $this->domainServerSocketFilePath = $builder->getDomainServerSocketFilePath();
        $this->maxByteForRead = $builder->getMaxByteForRead();
        $this->protocolFamilyForSocket = $builder->getProtocolFamilyForSocket();
        $this->typeOfDataExchange = $builder->getTypeOfDataExchange();
        $this->protocol = $builder->getProtocol();
        $this->logger = $logger;
    }

    public function socketCreate() {
        $socket = socket_create($this->protocolFamilyForSocket, $this->typeOfDataExchange, $this->protocol);
        if (!$socket) {
            $this->logger->log('Ошибка создания сокета');
            throw new SocketException('Ошибка создания сокета');
        }
        return $socket;
    }


    public function socketBind($socket) {
        $bind = socket_bind($socket, $this->domainServerSocketFilePath, 0);
        if (!$bind) {
            $this->logger->log('Не получилось связать дискриптор сокета с файлом доменного сокета Unix');
            throw new SocketException('Не получилось связать дискриптор сокета с файлом доменного сокета Unix');
        }
        return $bind;
    }

    /**
     * @param $socket
     * @return bool
     * @throws SocketException
     */
    public function socketListen($socket) {
        $phone = socket_listen($socket);
        if (!$phone) {
            $this->logger->log('Ошибка при попытке прослушивания сокетам');
            throw new SocketException('Ошибка при попытке прослушивания сокета');
        }
        return $phone;
    }

    public function read($socket) {
        $bytes = socket_recv($socket, $message, $this->maxByteForRead, 0);
        if (false === $bytes) {
            throw new SocketException('Ошибка при чтении сообщения');
        }
        return $message;
    }

    public function write($socket, $msg) {
        socket_write($socket, $msg, mb_strlen($msg, 'cp1251'));
    }

    public function socketClose($socket) {
        socket_close($socket);
        unlink($this->domainServerSocketFilePath);
    }

    /**
     * @param $socket
     * @return resource
     * @throws SocketException
     */
    public function startConnectionWithSocket($socket) {
        $socketConnection = socket_accept($socket);
        if (!$socketConnection) {
            $this->logger->log('Ошибка при старте соединений с сокетом');
            throw new SocketException('Ошибка при старте соединений с сокетом');
        }
        return $socketConnection;
    }
}
