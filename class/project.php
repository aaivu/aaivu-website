<?php

class Project
{
    private int $id;
    private String $name;
    private String $about;
    private String $summary;
    private array $contributors;
    private static array $project = array();

    private function __construct(int $id)
    {
        $sql = "SELECT * FROM `Project` WHERE ProjectId = $id";
        $result = QueryExecutor::query($sql);
        $row = $result->fetch_assoc();

        $this->id = $row["ProjectId"];
        $this->name = $row['Name'];
        $this->about = $row["About"];
        $this->summary = $row['Summary'];
    }

    public static function getInstance(int $id): Project|null
    {
        if (!array_key_exists($id, self::$project)) {
            $sql = "SELECT ProjectId FROM `Project` WHERE ProjectId = $id";
            if (QueryExecutor::query($sql)->num_rows == 0) {
                return null;
            } else {
                self::$project[$id] = new Project($id);
            }
        }
        return self::$project[$id];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): String
    {
        return $this->name;
    }

    public function setName(String $name): void
    {
        $sql = "UPDATE `Project` SET `Name`= '$name' WHERE `ProjectId` =  $this->id";
        QueryExecutor::query($sql);
        $this->name = $name;
    }

    public function getAbout(): String
    {
        return $this->about;
    }

    public function setAbout(String $about): void
    {
        $sql = "UPDATE `Project` SET `About`= '$about' WHERE `ProjectId` =  $this->id";
        QueryExecutor::query($sql);
        $this->about = $about;
    }

    public function getSummary(): String
    {
        return $this->summary;
    }

    public function setSummary(String $summary): void
    {
        $sql = "UPDATE `Project` SET `Summary`= '$summary' WHERE `ProjectId` =  $this->id";
        QueryExecutor::query($sql);
        $this->summary = $summary;
    }

    public function getContributors(): array
    {
        return $this->contributors;
    }

    public function addContributors(Contributor $c): void
    {
    }
}