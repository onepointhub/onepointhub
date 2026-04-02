<?php

namespace App\Modules\Core\Exceptions;

use RuntimeException;

class WorkspaceNotResolvedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(
            'A workspace must be resolved before querying workspace-scoped models. '.
            'Ensure WorkspaceMiddleware is applied to this route.'
        );
    }
}
