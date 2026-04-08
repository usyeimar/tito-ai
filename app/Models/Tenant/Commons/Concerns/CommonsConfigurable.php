<?php

namespace App\Models\Tenant\Commons\Concerns;

interface CommonsConfigurable
{
    /**
     * @return array{type_column: string, id_column: string, module: string, fields: list<string>}
     */
    public function getMutationConfig(): array;
}
