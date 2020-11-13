<?php


namespace BrosSquad\DotEnv;


use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Exception;
use SplFileObject;

class File extends SplFileObject
{
    /**
     * File constructor.
     *
     * @param        $file_name
     * @param string $open_mode
     * @param bool   $use_include_path
     * @param null   $context
     */
    public function __construct(string $file_name, string $open_mode = 'r', bool $use_include_path = false, $context = NULL)
    {
        parent::__construct($file_name, $open_mode, $use_include_path, $context);
    }


    /**
     * @inheritDoc
     * @return \Carbon\CarbonInterface
     */
    public function getCTime(): CarbonInterface
    {
        return CarbonImmutable::createFromTimestamp(parent::getCTime());
    }

    /**
     * @inheritDoc
     * @return \Carbon\CarbonInterface
     */
    public function getMTime(): CarbonInterface
    {
        return CarbonImmutable::createFromTimestamp(parent::getMTime());
    }

    /**
     * @inheritDoc
     * @return \Carbon\CarbonImmutable
     */
    public function getATime(): CarbonImmutable
    {
        return CarbonImmutable::createFromTimestamp(parent::getATime());
    }

    /**
     * @param string $fileName
     *
     * @return \Dusan\PhpMvc\File\File
     */
    public static function openForRead(string $fileName): File
    {
        return new self($fileName);
    }

    /**
     * @param string $fileName
     *
     * @return \Dusan\PhpMvc\File\File
     */
    public static function openForWrite(string $fileName): File
    {
        return new self($fileName, 'w');
    }

    public static function openForAppend(string $fileName): File
    {
        return new self($fileName, 'a');
    }

    public static function openForReadWrite(string $fileName): File
    {
        return new self($fileName, 'rw+');
    }

    public function write(string $str, ?int $length = NULL): int
    {
        return parent::fwrite($str, $length);
    }

    public function writeArray(array $data): int
    {
        $output = join("\n", $data);
        return $this->write($output);
    }

    /**
     * This method acquires the shared lock -> best used for reading from a file
     * if this method fails to acquire the shared lock, Exception is thrown
     * Parameter supplied to the method has to be callable or Closure, when this parameter is supplied
     * this method will invoke unlock automatically (proffered way of doing)
     * return of the callback will be also the return of this method, if no callback is supplied then
     * the return will be void
     *
     * @param callable|null $fn
     *
     * @return mixed|void
     * @throws \Exception
     */
    public function sharedLock(?callable $fn = NULL)
    {
        if (!$this->flock(LOCK_SH)) {
            throw new Exception('Shared lock could not be acquired');
        }

        if ($fn !== NULL) {
            $value = $fn();
            $this->unlock();
            return $value;
        }
    }

    /**
     * This method acquires the exclusive lock -> best used for writing to a file
     * if this method fails to acquire the Exclusive lock, Exception is thrown
     * Parameter supplied to the method has to be callable or Closure, when this parameter is supplied
     * this method will invoke unlock automatically (proffered way of doing)
     * return of the callback will be also the return of this method, if no callback is supplied then
     * the return will be void
     *
     * @param callable|null $fn
     *
     * @return mixed|void
     * @throws \Exception
     */
    public function exclusiveLock(?callable $fn = NULL)
    {
        if (!$this->flock(LOCK_EX)) {
            throw new Exception('Exclusive lock could not be acquired');
        }
        if ($fn !== NULL) {
            $value = $fn();
            $this->unlock();
            return $value;
        }
    }


    /**
     * This function is used to release the lock acquired by the $this->flock(), $this->exclusiveLock()
     * or $this->sharedLock
     * boolean is returned as the result
     *
     * @return bool
     */
    public function unlock()
    {
        return $this->flock(LOCK_UN);
    }
}
