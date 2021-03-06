<?php

class SqlUtilsTest extends \PHPUnit\Framework\TestCase
{
    public function testQueryToStatementsReturnsOne()
    {
        $utils = new SqlUtils();
        $statements = $utils->queryToStatements("select foo from bar");
        $this->assertCount(1, $statements);

        $statements = $utils->queryToStatements("select foo from bar;");
        $this->assertCount(1, $statements);
        $this->assertEquals("select foo from bar", $statements[0]);
    }

    public function testQueryToStatementsReturnsTwo()
    {
        $utils = new SqlUtils();
        $statements = $utils->queryToStatements(
            "#foo\nselect foo from bar; select hello from world;foo"
        );

        $this->assertEquals("\nselect foo from bar", $statements[0]);
        $this->assertEquals(" select hello from world", $statements[1]);
        $this->assertEquals("foo", $statements[2]);
        $this->assertCount(3, $statements);
    }
}
