<?php

namespace App\Database\Firebird;

use App\Database\Firebird\Query\Grammars\FirebirdGrammar as CustomFirebirdQueryGrammar;
use App\Database\Firebird\Query\Processors\FirebirdProcessor as CustomFirebirdQueryProcessor;
use App\Database\Firebird\Schema\Grammars\FirebirdGrammar as CustomFirebirdSchemaGrammar;
use HarryGulliford\Firebird\FirebirdConnection as BaseFirebirdConnection;

class FirebirdConnection extends BaseFirebirdConnection
{
    /**
     * Get the default schema grammar instance.
     *
     * @return \HarryGulliford\Firebird\Schema\Grammars\FirebirdGrammar
     */
    protected function getDefaultSchemaGrammar()
    {
        return $this->withTablePrefix(new CustomFirebirdSchemaGrammar);
    }

    /**
     * Get the default query grammar instance.
     *
     * @return \Illuminate\Database\Query\Grammars\Grammar
     */
    protected function getDefaultQueryGrammar()
    {
        return $this->withTablePrefix(new CustomFirebirdQueryGrammar);
    }

    /**
     * Get the default post processor instance.
     *
     * @return \Illuminate\Database\Query\Processors\Processor
     */
    protected function getDefaultPostProcessor()
    {
        return new CustomFirebirdQueryProcessor;
    }

    /**
     * Determine if the connection is currently in a transaction.
     *
     * @return bool
     */
    public function inTransaction()
    {
        return $this->getPdo()->inTransaction();
    }

    /**
     * Begin a new database transaction.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function beginTransaction()
    {
        // If already in a transaction, don't start a new one (Firebird doesn't support nested transactions)
        if ($this->inTransaction()) {
            return;
        }

        $this->getPdo()->beginTransaction();
        $this->fireConnectionEvent('beganTransaction');
    }

    /**
     * Commit the active database transaction.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function commit()
    {
        if (!$this->inTransaction()) {
            return;
        }

        $this->getPdo()->commit();
        $this->fireConnectionEvent('committed');
    }

    /**
     * Rollback the active database transaction.
     *
     * @param  int|null  $toLevel
     * @return void
     *
     * @throws \Exception
     */
    public function rollBack($toLevel = null)
    {
        if (!$this->inTransaction()) {
            return;
        }

        $this->getPdo()->rollBack();
        $this->fireConnectionEvent('rollingBack');
    }
}