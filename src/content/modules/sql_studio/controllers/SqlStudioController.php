<?php
use UliCMS\Exceptions\SqlException;
use UliCMS\HTML\Alert;

class SqlStudioController extends MainClass
{
    const MODULE_NAME = "sql_studio";

    public function settings()
    {
        ViewBag::set("tables", Database::getAllTables());
        return Template::executeModuleTemplate(self::MODULE_NAME, "form.php");
    }

    public function getSettingsHeadline()
    {
        return "SQL Studio";
    }
    
    public function adminHeadStylesFilter($styles)
    {
        $styles[] = ModuleHelper::buildModuleRessourcePath(
            self::MODULE_NAME,
            "css/style.css"
        );
        return $styles;
    }

    public function saveSettings()
    {
        $replace_placeholders = intval(Request::getVar("replace_placeholders", 0, "int"));
        Settings::set("sql_studio/replace_placeholders", $replace_placeholders);
        
        $replace_placeholders = Request::getVar("table_name_onclick_action", "generate_and_execute_select_statement", "str");
        Settings::set("sql_studio/table_name_onclick_action", $replace_placeholders);
        
        Response::redirect(ModuleHelper::buildAdminURL(self::MODULE_NAME));
    }

    public function executeSql()
    {
        $sql = Request::getVar("sql_code", "", "str");
        if (StringHelper::isNullOrWhitespace($sql)) {
            HtmlResult("");
        }
        
        $html = "";
        
        $replacePlaceholders = boolval(Settings::get("sql_studio/replace_placeholders"));
        
        $sqlUtils = new SqlUtils();
        $statements = $sqlUtils->queryToStatements($sql);
        
        foreach ($statements as $statement) {
            try {
                $result = @Database::query($statement, $replacePlaceholders);
                if (! $result || Database::getError()) {
                    ViewBag::set("error", Database::getError());
                    $html .= Template::executeModuleTemplate(self::MODULE_NAME, "error.php");
                } elseif (is_bool($result) and $result) {
                    $affectedRows = Database::getAffectedRows();
                    ViewBag::set("success", get_translation("x_rows_affected", array(
                        "%x" => $affectedRows
                    )));
                    $html .= Template::executeModuleTemplate(self::MODULE_NAME, "success.php");
                } else {
                    ViewBag::set("result", $result);
                    // Mock, TODO: Split sql statements, show multiple tables
                    $html .= Template::executeModuleTemplate(self::MODULE_NAME, "table.php");
                }
            } catch (SqlException $e) {
                $html .= Alert::danger($e->getMessage());
            }
        }
        HTMLResult($html);
    }
}
