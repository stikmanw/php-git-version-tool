<?php
namespace Common\Git;

class GitWrapper
{
    /**
     * @var string
     */
    protected $git = '/usr/bin/git';

    /**
     * @param string $git
     */
    public function setGit($git)
    {
        $this->git = $git;
    }

    /**
     * @return string
     */
    public function getGit()
    {
        return $this->git;
    }

    /**
     * This will clone a remote directory with the no checkout option on the current box
     * @param $path
     * @param $source
     * @return bool
     * @throws \RuntimeException
     */
    public function cloneRemoteNoCheckout($path, $source)
    {
        $sourceArg = escapeshellarg($source);
        $pathArg = escapeshellarg($path);
        $command = "{$this->git} clone --no-checkout $sourceArg $pathArg 2>&1";

        $output = array();
        exec($command, $output);

        if(list($error, $detail) = $this->detectError($output)) {
            $exception = new GitException("Git error occurred: $error, $detail");
            $exception->setCommand($command);
            throw $exception;
        }

        return true;
    }

    public function add($path, $modulePath)
    {
        $modulePathArg = escapeshellarg($modulePath);
        $command = "{$this->git} {$this->gitDirPathOption($path)} {$this->gitWorkTreeOption($path)} add {$modulePathArg} 2>&1";

        $output = array();
        exec($command, $output);

        if(list($error, $detail) = $this->detectError($output)) {
            $exception = new GitException("Git error occurred: $error, $detail");
            $exception->setCommand($command);
            throw $exception;
        }

        return true;
    }


    public function commitAndPush($path, $modulePath, $message = "generated commit")
    {
        $modulePathArg = escapeshellarg($modulePath);
        $messageArg = escapeshellarg($message);
        $command =
            "{$this->git} {$this->gitDirPathOption($path)} {$this->gitWorkTreeOption($path)} commit {$modulePathArg}" .
            " -m {$messageArg} 2>&1";

        $output = array();
        exec($command, $output);
        if(list($error, $detail) = $this->detectError($output)) {
            $exception = new GitException("Git error occurred: $error, $detail");
            $exception->setCommand($command);

            throw $exception;
        }

        $command =  "{$this->git} {$this->gitDirPathOption($path)} {$this->gitWorkTreeOption($path)} push 2>&1";
        exec($command, $output);

        if(list($error, $detail) = $this->detectError($output)) {
            $exception = new GitException("Git error occurred: $error, $detail");
            $exception->setCommand($command);
            throw $exception;
        }

        return true;
    }

    function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
    }

    /**
     * Checkout a reference to a specific repository
     * @param $path
     * @param $reference
     * @return bool
     * @throws GitException
     */
    public function checkoutRef($path, $reference)
    {
        $refArg = escapeshellarg($reference);
        $command = "{$this->git} {$this->gitDirPathOption($path)} {$this->gitWorkTreeOption($path)} checkout {$refArg} 2>&1";

        $output = array();
        exec($command . " 2>&1", $output);

        if(list($error, $detail) = $this->detectError($output)) {
            $exception = new GitException("Git error occurred: $error, $detail");
            $exception->setCommand($command);
            throw $exception;
        }

        return true;
    }

    /**
     * If the given repo has a specific tag then return the reference hash
     *
     * @param string $path path to the repo
     * @param string $tag semver tag name
     * @return bool | string
     * @throws GitException
     */
    public function getTagRef($path, $tag)
    {
        $tagArg = escapeshellarg($tag);
        $command = "{$this->git} {$this->gitDirPathOption($path)} show-ref {$tagArg} 2>&1";

        $output = array();
        exec($command, $output);

        if(list($error, $detail) = $this->detectError($output)) {
            $exception = new GitException("Git error occurred: $error, $detail");
            $exception->setCommand($command);
            throw $exception;
        }

        if(empty($output)) {
            return false;
        }

        preg_match("#([a-f0-9]+) refs/tags/{$tag}$#", current($output), $matches);

        // some type of output match error occurred and we could not find a hash
        if(empty($matches)) {
            return false;
        } else {
            return $matches[1];
        }

    }

    /**
     * @param $path
     * @return bool|mixed
     * @throws GitException
     */
    public function getMasterRef($path)
    {
        $command = "{$this->git} {$this->gitDirPathOption($path)} rev-parse master 2>&1";

        $output = array();
        exec($command, $output);
        if(list($error, $detail) = $this->detectError($output)) {
            $exception = new GitException("Git error occurred: $error, $detail");
            $exception->setCommand($command);
            throw $exception;
        }

        if(is_array($output) && preg_match("#[a-f0-9]+#", current($output))) {
            return current($output);
        } else {
            return false;
        }
    }

    /**
     * Options used to set where git references are stored on filesystem
     * @param $path
     * @return string
     */
    public function gitDirPathOption($path)
    {
        return "'--git-dir'='{$path}.git'";
    }

    /**
     * Option used to set active working directory for git commands
     * @param $path
     * @return string
     */
    public function gitWorkTreeOption($path)
    {
        return "'--work-tree'='{$path}'";
    }

    /**
     * Detect a git command error using a couple common expressions
     * pass in the results from an exec command
     * @param $output
     * @return array | false
     */
    public function detectError($output)
    {
        $output = implode(PHP_EOL, $output);
        foreach($this->getError() as $errorEx) {
            if(preg_match("#{$errorEx}#", $output, $detail)) {
                return array($detail[1], $detail[2]);
            }
        }

        return false;
    }

    /**
     * List of expressions to check the exec output for.
     * @return array
     */
    protected function getError()
    {
        return array(
           '^(fatal\:)(.+)',

           '^(Unknown option)(.*)\n?'
        );
    }

}
