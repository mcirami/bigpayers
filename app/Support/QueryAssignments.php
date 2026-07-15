<?php

namespace App\Support;

use Exception;

class QueryAssignments
{
    protected array $assignments = [];
    protected array $required = [];
    public string $redirectURL = '/dashboard';
    public bool $cleanData = false;

    public function __construct(array $assignedVars, $customRedirect = false, bool $cleanData = true)
    {
        $this->assignments = $assignedVars;

        if ($customRedirect) {
            $this->redirectURL = $customRedirect;
        }

        $this->findRequired();
        $this->cleanData = $cleanData;
    }

    public function clean(): void
    {
        $query = NativeRequest::queryAll();

        foreach ($this->assignments as $key => $value) {
            if (isset($query[$key])) {
                $this->assignments[$key] = xss_clean($query[$key]);
            }
        }
    }

    public function buildJSONArray($ignore = false)
    {
        return json_encode($ignore ? $this->removeKeys($this->assignments, $ignore) : $this->assignments);
    }

    public function setGlobals(): void
    {
        foreach ($this->assignments as $key => $value) {
            $GLOBALS[$key] = $value;
        }
    }

    public function __get($varName)
    {
        if (!isset($this->assignments[$varName])) {
            throw new Exception('Undefined variable ' . $varName);
        }

        return $this->assignments[$varName];
    }

    public function __set($varName, $value): void
    {
        $this->assignments[$varName] = $value;
    }

    public function set($varName, $value): void
    {
        $this->assignments[$varName] = $value;
    }

    public function getAssignments(): void
    {
        $this->checkRequired();
        $query = NativeRequest::queryAll();

        foreach ($this->assignments as $key => $value) {
            if (isset($query[$key])) {
                $this->assignments[$key] = $query[$key];
            }
        }

        if ($this->cleanData) {
            $this->clean();
        }
    }

    public function has($keyName): bool
    {
        return array_key_exists($keyName, $this->assignments);
    }

    public function get($keyName)
    {
        return $this->assignments[$keyName];
    }

    public function buildAssignments($ignore = false): string
    {
        $assignments = $ignore ? $this->removeKeys($this->assignments, $ignore) : $this->assignments;
        $url = '?';

        foreach ($assignments as $key => $value) {
            $url .= ($url === '?' ? '' : '&') . $key . '=' . $value;
        }

        if (NativeRequest::hasQuery('adminLogin')) {
            $url .= '&adminLogin';
        }

        return $url;
    }

    public function getRedirectURL(): string
    {
        return $this->redirectURL;
    }

    public function setRedirect($url): void
    {
        $this->redirectURL = $url;
    }

    public function redirect(): void
    {
        send_to($this->redirectURL);
    }

    private function removeKeys(array $assignments, array $keys): array
    {
        foreach ($keys as $key) {
            unset($assignments[$key]);
        }

        return $assignments;
    }

    private function findRequired(): void
    {
        foreach ($this->assignments as $key => $value) {
            if (str_starts_with($key, '!')) {
                $name = substr($key, 1);
                $this->required[$name] = true;
                $this->assignments[$name] = '!';
                unset($this->assignments[$key]);
            }
        }
    }

    private function checkRequired(): void
    {
        $query = NativeRequest::queryAll();

        foreach (array_keys($this->required) as $key) {
            if (!isset($query[$key]) || $query[$key] == null) {
                $this->redirect();
            }
        }
    }
}
