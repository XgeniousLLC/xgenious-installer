<?php

namespace Xgenious\Installer\Tests\Feature;

use Xgenious\Installer\Helpers\InstallationHelper;
use Xgenious\Installer\Tests\TestCase;

class InstallationHelperSqlStatementsTest extends TestCase
{
    public function test_dump_is_split_into_statements_without_breaking_quoted_semicolons(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'sql');
        file_put_contents($path, <<<'SQL'
-- header comment
SET time_zone = "+00:00";

/*!40101 SET NAMES utf8mb4 */;

INSERT INTO `t` (`a`) VALUES
('one; two'),
('it\'s; fine'),
('it''s; also');
COMMIT;
SQL);

        $method = new \ReflectionMethod(InstallationHelper::class, 'sql_statements');
        $method->setAccessible(true);
        $statements = array_map('trim', iterator_to_array($method->invoke(null, $path), false));
        unlink($path);

        $this->assertCount(4, $statements);
        $this->assertSame('SET time_zone = "+00:00";', $statements[0]);
        $this->assertStringStartsWith('INSERT INTO `t`', $statements[2]);
        $this->assertStringContainsString("('it''s; also');", $statements[2]);
        $this->assertSame('COMMIT;', $statements[3]);
    }
}
