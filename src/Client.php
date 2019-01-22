<?php

namespace harlam\Ftp;

use harlam\Ftp\Exceptions\FtpChangeDirectoryException;
use harlam\Ftp\Exceptions\FtpChangeModeException;
use harlam\Ftp\Exceptions\FtpConnectionException;
use harlam\Ftp\Exceptions\FtpDeleteException;
use harlam\Ftp\Exceptions\FtpFileChangeModeException;
use harlam\Ftp\Exceptions\FtpFileDownloadException;
use harlam\Ftp\Exceptions\FtpFileUploadException;
use harlam\Ftp\Exceptions\FtpLoginException;
use harlam\Ftp\Exceptions\FtpPwdException;

class Client
{
    private $connection;

    public function __construct(string $host, int $port = 21, int $timeout)
    {
        $this->connection = @ftp_connect($host, $port, $timeout);
        if ($this->connection === false) {
            throw new FtpConnectionException("Connection failed to '{$host}:{$port}'");
        }
    }

    public function __destruct()
    {
        ftp_close($this->connection);
    }

    /**
     * @param string $username Username
     * @param string $password Password
     * @throws FtpLoginException
     * @return Client
     */
    public function login(string $username, string $password): Client
    {
        if (!@ftp_login($this->connection, $username, $password)) {
            throw new FtpLoginException("Authentication failed with username '{$username}'");
        }
        return $this;
    }

    /**
     * @throws FtpChangeDirectoryException
     * @return Client
     */
    public function cdup(): Client
    {
        if (!@ftp_cdup($this->connection)) {
            throw new FtpChangeDirectoryException("Change directory to '/' failed");
        }
        return $this;
    }

    /**
     * @param bool $pasv
     * @throws FtpChangeModeException
     * @return Client
     */
    public function pasv(bool $pasv = true): Client
    {
        if (!@ftp_pasv($this->connection, $pasv)) {
            $modeLabel = $pasv === true ? 'PASSIVE' : 'ACTIVE';
            throw new FtpChangeModeException("Change mode to '{$modeLabel}' failed");
        }
        return $this;
    }

    /**
     * @param string $directory
     * @throws FtpChangeDirectoryException
     * @return Client
     */
    public function chdir(string $directory): Client
    {
        if (!@ftp_chdir($this->connection, $directory)) {
            throw new FtpChangeDirectoryException("Change directory to '{$directory}' failed");
        }
        return $this;
    }

    /**
     * @param string $directory
     * @return array
     */
    public function nlist(string $directory = '.'): array
    {
        if (($result = @ftp_nlist($this->connection, $directory)) === false) {
            return [];
        }
        return $result;
    }

    /**
     * @param int $mode
     * @param string $filename
     * @throws FtpFileChangeModeException
     * @return array
     */
    public function chmod(int $mode, string $filename): array
    {
        if (($result = @ftp_chmod($this->connection, $mode, $filename)) === false) {
            throw new FtpFileChangeModeException("Change mode '{$mode}' for '{$filename}' failed");
        }
        return $result;
    }

    /**
     * @param string $local_file
     * @param string $remote_file
     * @param int $mode
     * @param int $resumepos
     * @throws FtpFileDownloadException
     * @return Client
     */
    public function get(string $local_file, string $remote_file, int $mode = FTP_BINARY, int $resumepos = 0): Client
    {
        if (!@ftp_get($this->connection, $local_file, $remote_file, $mode, $resumepos)) {
            throw new FtpFileDownloadException("Downloading file '{$remote_file}' to '{$local_file}' failed");
        }
        return $this;
    }

    /**
     * @param string $remote_file
     * @param string $local_file
     * @param int $mode
     * @param int $startpos
     * @throws FtpFileUploadException
     * @return Client
     */
    public function put(string $remote_file, string $local_file, int $mode = FTP_IMAGE, int $startpos = 0): Client
    {
        if (!@ftp_put($this->connection, $remote_file, $local_file, $mode, $startpos)) {
            throw new FtpFileUploadException("Uploading file '{$local_file}' to '{$remote_file}' failed");
        }
        return $this;
    }

    /**
     * @throws FtpPwdException
     * @return string
     */
    public function pwd(): string
    {
        if (($result = @ftp_pwd($this->connection)) === false) {
            throw new FtpPwdException();
        }
        return $result;
    }

    /**
     * @param string $path
     * @throws FtpDeleteException
     * @return Client
     */
    public function delete(string $path): Client
    {
        if (!@ftp_delete($this->connection, $path)) {
            throw new FtpDeleteException("Delete file '{$path}' failed");
        }
        return $this;
    }
}