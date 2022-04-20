<?php

class Contributor
{
    private int $id;
    private String $name;
    private String $profilePic;
    private String $about;
    private static array $contributor = array();

    private function __construct(int $id)
    {
        $sql = "SELECT * FROM `Contributor` WHERE ContributorId = $id";
        $result = QueryExecutor::query($sql);
        $row = $result->fetch_assoc();

        $this->id = $row["ContributorId"];
        $this->name = $row['Name'];
        $this->profilePic = $row['ProfilePic'];
        $this->about = $row["About"];
    }

    public static function getInstance(int $id): Contributor|null
    {
        if (!array_key_exists($id, self::$contributor)) {
            $sql = "SELECT ContributorId FROM `Contributor` WHERE ContributorId = $id";
            if (QueryExecutor::query($sql)->num_rows == 0) {
                return null;
            } else {
                self::$contributor[$id] = new Contributor($id);
            }
        }
        return self::$contributor[$id];
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
        $sql = "UPDATE `Contributor` SET `Name`= '$name' WHERE `ContributorId` =  $this->id";
        QueryExecutor::query($sql);
        $this->name = $name;
    }

    public function getAbout(): String
    {
        return $this->about;
    }

    public function setAbout(String $about): void
    {
        $sql = "UPDATE `Contributor` SET `About`= '$about' WHERE `ContributorId` =  $this->id";
        QueryExecutor::query($sql);
        $this->about = $about;
    }

    public function getProfilePic(): String
    {
        return $this->profilePic;
    }

    public function setProfilePic(String $profilePic): void
    {
        $sql = "UPDATE `Contributor` SET `ProfilePic`= '$profilePic' WHERE `ContributorId` =  $this->id";
        QueryExecutor::query($sql);
        $this->profilePic= $profilePic;
    }
}
