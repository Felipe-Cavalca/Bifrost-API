<?php

namespace Bifrost\Core;

use Bifrost\Integration\Database\PdoDatabase;
use Bifrost\Interface\Database as DatabaseInterface;

class Database extends PdoDatabase implements DatabaseInterface
{
}
