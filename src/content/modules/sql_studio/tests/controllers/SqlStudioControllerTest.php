<?php

use Spatie\Snapshots\MatchesSnapshots;

class SqlStudioControllerTest extends \PHPUnit\Framework\TestCase
{
    use MatchesSnapshots;

    protected function setUp(): void
    {
        require_once getLanguageFilePath("en");
        Translation::loadAllModuleLanguageFiles("en");
        $_SESSION["login_id"] = $this->getAdmin()->getId();
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    public function testSettings()
    {
        $controller = new SqlStudioController();
        $html = $controller->settings();
        $this->assertStringContainsString(
            '<form action="index.php" method="post" class="sql-studio-ui">',
            $html
        );

        $tables = Database::getAllTables();
        foreach ($tables as $table) {
            $this->assertStringContainsString($table, $html);
        }
    }

    public function testGetSettingsHeadline()
    {
        $controller = new SqlStudioController();
        $this->assertMatchesTextSnapshot($controller->getSettingsHeadline());
    }

    public function testAdminHeadStylesFilter()
    {
        $controller = new SqlStudioController();
        $styles = ["foo1", "foo2"];
        $processedStyles = $controller->adminHeadStylesFilter($styles);

        $this->assertCount(3, $processedStyles);
        $this->assertStringEndsWith(
            "sql_studio/css/style.css",
            $processedStyles[2]
        );
    }

    protected function getAdmin(): User
    {
        $manager = new UserManager();
        $user = $manager->getAllUsers("admin desc")[0];
        $user->setSecondaryGroups([]);
        $user->save();
        return $user;
    }
}
