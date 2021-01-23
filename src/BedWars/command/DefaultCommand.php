<?php

namespace BedWars\command;

use BedWars\BedWars;
use BedWars\game\Game;
use BedWars\utils\Utils;
use BedWars\libs\jojoe77777\FormAPI\CustomForm;
use BedWars\libs\jojoe77777\FormAPI\SimpleForm;
use BedWars\libs\jojoe77777\FormAPI\Form;
use pocketmine\plugin\Plugin;
use pocketmine\Player;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\block\Block;
use pocketmine\block\TNT;
use pocketmine\item\Bed;
use pocketmine\level\Level;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\math\Vector3;

class DefaultCommand extends PluginCommand {
	
	/** @var BedWars $plugin */
	private $plugin;
    /** @var array $errors */
    private $cachedCommandResponse = [];
    /** @var array $cachedResponses */
    private $cachedFormResponse = [];

    /**
     * DefaultCommand constructor.
     * @param BedWars $plugin
     */
    public function __construct(BedWars $plugin) {
    	$this->plugin = $plugin;
        parent::__construct("bedwars", $plugin);
        $this->setPermission("bedwars.command");
        $this->setAliases(["bw"]);
        $this->setUsage("/bedwars");
        $this->setDescription("BedWars Command");
    }

    /**
     * @param Player $player
     * @param string $command
     * @return array|null
     */
    public function getErrorsForCommand(Player $player, string $command) : ?array {
        if(!isset($this->cachedCommandResponse[$player->getRawUniqueId()]))return null;
        $errors = $this->cachedCommandResponse[$player->getRawUniqueId()];
        if($errors['command'] == $command && count($errors['errors']) > 0){
                return $errors['errors'];
        }
        return null;
    }

    /**
     * @param Player $player
     * @param string $command
     * @return array|null
     */
    public function getValuesForCommand(Player $player, string $command) : ?array {
        if(!isset($this->cachedCommandResponse[$player->getRawUniqueId()]))return null;
        $values = $this->cachedCommandResponse[$player->getRawUniqueId()];
        if($values['command'] == $command && count($values['values']) > 0){
            return $values['values'];
        }
        return null;
    }

    public function sendFormCustom(Player $player, CustomForm $form, string $command) : void {
        $errors = $this->getErrorsForCommand($player, $command);
        $values = $this->getValuesForCommand($player, $command);
        $form->setTitle("BedWars: Setup Manager");
        switch ($command){
            case "create";
            $form->addInput(isset($errors[0]) ? "GameID: " . $errors[0] : "Game ID", "String/Integer", isset($values[0]) ? $values[0] : "");
            $form->addInput(isset($errors[1]) ? "Minimum players: " . $errors[1] : "Minimum players", "Integer", isset($values[1]) ? $values[1] : "");
            $form->addInput(isset($errors[2]) ? "Players per team: " . $errors[2] : "Players per team", "Integer", isset($values[2]) ? $values[2] : "");
            $form->addInput(isset($errors[3]) ? "Start time: " . $errors[3] : "Start time", "Integer", isset($values[3]) ? $values[3] : "");
            $form->addInput(isset($errors[4]) ? "Map name: " . $errors[4] : "Map name", "String", isset($values[4]) ? $values[4] : "");
            $form->sendToPlayer($player);
            break;
            case 'addteam';
            $form->addInput(isset($errors[0]) ? "GameID: " . $errors[0] : "Game ID", "String/Integer", isset($values[0]) ? $values[0] : "");
            $form->addDropdown(isset($errors[1]) ? "Team: " . $errors[1] : "Team", array_keys(BedWars::TEAMS), isset($values[1]) ? $values[1] : null);
            $form->sendToPlayer($player);
            break;
            case "delete";
            $form->addInput(isset($errors[0]) ? "GameID: " . $errors[0] : "Game ID", "String/Integer", isset($values[0]) ? $values[0] : "");
            $form->sendToPlayer($player);
            break;
            case "setlobby";
            $form->addInput(isset($errors[0]) ? "GameID: " . $errors[0] : "Game ID", "String/Integer", isset($values[0]) ? $values[0] : "");
            $form->addInput(isset($errors[1]) ? "Coord X: " . $errors[0] : "Coord X", "Integer/Float", isset($values[1]) ? $values[1] : $player->getX());
            $form->addInput(isset($errors[2]) ? "Coord Y: " . $errors[0] : "Coord Y", "Integer/Float", isset($values[2]) ? $values[2] : $player->getY());
            $form->addInput(isset($errors[3]) ? "Coord Z: " . $errors[0] : "Coord Z", "Integer/Float", isset($values[3]) ? $values[3] : $player->getZ());
            $form->addInput(isset($errors[4]) ? "Level name: " . $errors[4] : "Level name", "String", isset($values[4]) ? $values[4] : "");
            $form->sendToPlayer($player);
            break;
            case "setposition";
            $form->addInput(isset($errors[0]) ? "GameID: " . $errors[0] : "Game ID", "String/Integer", isset($values[0]) ? $values[0] : "");
            $form->addDropdown(isset($errors[1]) ? "Team: " . $errors[1] : "Team", array_keys(BedWars::TEAMS), isset($values[1]) ? $values[1] : null);
            $form->addDropdown("Position", array('ShopClassic', 'ShopUpgrades', 'Spawn'), isset($values[2]) ? $values[2] : null);
            $form->addInput(isset($errors[0]) ? "Coord X: " . $errors[0] : "Coord X", "Integer/Float", isset($values[1]) ? $values[1] : $player->getX());
            $form->addInput(isset($errors[0]) ? "Coord Y: " . $errors[0] : "Coord Y", "Integer/Float", isset($values[2]) ? $values[2] : $player->getY());
            $form->addInput(isset($errors[0]) ? "Coord Z: " . $errors[0] : "Coord Z", "Integer/Float", isset($values[3]) ? $values[3] : $player->getZ());
            $form->sendToPlayer($player);
            break;
            case "setbed";
            $form->addInput(isset($errors[0]) ? "GameID: " . $errors[0] : "Game ID", "String/Integer", isset($values[0]) ? $values[0] : "");
            $form->addDropdown(isset($errors[1]) ? "Team: " . $errors[1] : "Team", array_keys(BedWars::TEAMS), isset($values[1]) ? $values[1] : null);
            $form->sendToPlayer($player);
            break;
            case "setgenerator";
            $form->addInput(isset($errors[0]) ? "GameID: " . $errors[0] : "Game ID", "String/Integer", isset($values[0]) ? $values[0] : "");
            $form->addDropdown(isset($errors[2]) ? "Type: " . $errors[2] : "Type", array("Diamond", "Emerald", "Gold", "Iron"), isset($values[2]) ? $values[2] : null);
            $form->sendToPlayer($player);
            break;
        }
        if($errors !== null) {
            unset($this->cachedCommandResponse[$player->getRawUniqueId()]);
        }
        $this->cachedFormResponse[$command] = $form;
        $refOb = new \ReflectionObject($this->cachedFormResponse[$command]);
        $property = $refOb->getProperty('data');
        $property->setAccessible(true);
        $clonedData = $property->getValue($this->cachedFormResponse[$command]);
        $clonedData['content'] = [];
        $property->setValue($this->cachedFormResponse[$command], $clonedData);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return bool|mixed|void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if(empty($args[0])){
            return;
        }
        switch(strtolower($args[0])){
            case "list";
            commandList:
            $listForm = new SimpleForm(function (Player $player, ?array $data){
                if($data === null) {
                    return;
                }
                $gameClicked = $this->plugin->games[$data];
            });
            foreach($this->plugin->games as $game){
                $listForm->addButton(TextFormat::YELLOW . $game->getName() . "\n" . TextFormat::RESET . "Click to edit");
            }
            break;
            case "create";
            if(!$sender instanceof Player){
                $sender->sendMessage(TextFormat::RED . "This command can be executed only in game");
                return;
            }
            commandCreate:
            $createForm = new CustomForm(function(Player $player, ?array $data){
                if($data === null) {
                    return;
                }
                $error = [];
                if(isset($data[0]) && $data[0] !== ""){
                    if(strlen($data[0]) < 5){
                        $error[0] = TextFormat::RED . "Too short";
                        goto b;
                    }
                    if($this->plugin->gameExists($data[0])){
                        $error[0] = TextFormat::RED . "Already exists";
                    }
                } else {
                    $error[0] = TextFormat::RED . "Column can't be blank";
                }
                b:
                if(isset($data[1]) && $data[1] !== ""){
                    if(!is_int((int)$data[1])){
                        $error[1] = TextFormat::RED . "Must be an Integer";
                        goto c;
                    }
                    if((int)$data[1] < 1){
                        $error[1] = TextFormat::RED . "Must be higher than 0";
                    }
                } else {
                    $error[1] = TextFormat::RED . "Column can't be blank";
                }
                c:
                if(isset($data[2]) && $data[2] !== ""){
                    if(!is_int((int)$data[2])){
                        $error[2] = TextFormat::RED . "Must be an Integer";
                        goto d;
                    }
                    if((int)$data[2] < 1){
                        $error[2] = TextFormat::RED . "Must be higher than 0";
                    }
                } else {
                    $error[2] = TextFormat::RED . "Column can't be blank";
                }
                d:
                if(isset($data[3]) && $data[3] !== ""){
                    if(!is_int((int)$data[3])){
                        $error[3] = TextFormat::RED . "Must be an Integer";
                        goto e;
                    }
                    if((int)$data[3] < 1){
                        $error[3] = TextFormat::RED . "Must be higher than 0";
                    }
                } else {
                    $error[3] = TextFormat::RED . "Column can't be blank";
                }
                if(isset($data[4]) && $data[4] !== ""){
                    if(strlen($data[4]) <= 1){
                        $error[4] = TextFormat::RED . "Too short";
                    }
                    if(!$this->plugin->getServer()->loadLevel($data[4])){
                        $error[4] = TextFormat::RED . "Level not found or corrupt";
                    }
                    if(!$this->plugin->getServer()->isLevelLoaded($data[4])){
                        $error[4] = TextFormat::RED . "Level not loaded";
                    }
                } else {
                    $error[4] = TextFormat::RED . "Column can't be blank";
                }
                e:
                if(count($error) > 0){
                    $this->cachedCommandResponse[$player->getRawUniqueId()] = array('command' => 'create', 'errors' => $error, 'values' => $data);
                    $this->sendFormCustom($player, $this->cachedFormResponse['create'], 'create');
                } else {
                    $this->plugin->createGame($data[0], $data[1], $data[2], $data[3], $data[4]);
                    $player->sendMessage(TextFormat::GREEN . "Game created");
                }
            });
            $this->sendFormCustom($sender, $createForm, 'create');
            break;
            case "addteam";
            if(!$sender instanceof Player){
                $sender->sendMessage(TextFormat::RED . "This command can be executed only in game");
                return;
            }
            commandAddteam:
            $addteamForm = new CustomForm(function(Player $player, ?array $data){
                if($data === null){
                    return;
                }
                $error = [];
                if(isset($data[0]) && $data[0] !== ""){
                    if(!$this->plugin->gameExists($data[0])){
                        $error[0] = TextFormat::RED . "Doesn't exist";
                    }
                } else {
                    $error[0] = TextFormat::RED . "Column can't be blank";
                }
                if(isset($data[1]) && $data[1] !== ""){
                $find = false;
                    foreach(BedWars::TEAMS as $team2 => $color){
                        if($team2 === strtolower(array_keys(BedWars::TEAMS)[$data[1]])){
                            $find = true;
                        }
                    }
                    if(!$find){
                        $error[1] = TextFormat::RED . "Invalid team";
                    }
                    if($this->plugin->teamExists($data[0], array_keys(BedWars::TEAMS)[$data[1]])){
                        $error[1] = TextFormat::RED . "Already exists for " . $data[0];
                    }
                } else {
                    $error[1] = TextFormat::RED . "Column can't be blank";
                }
                if(count($error) > 0){
                    $this->cachedCommandResponse[$player->getRawUniqueId()] = array('command' => 'addteam', 'errors' => $error, 'values' => $data);
                    $this->sendFormCustom($player, $this->cachedFormResponse['addteam'], 'addteam');
                } else {
                    $this->plugin->addTeam($data[0], array_keys(BedWars::TEAMS)[$data[1]]);
                    $player->sendMessage(TextFormat::GREEN . "Team added");
                }
            });
            $this->sendFormCustom($sender, $addteamForm, 'addteam');
            break;
            case "delete";
            if(!$sender instanceof Player){
                $sender->sendMessage(TextFormat::RED . "This command can be executed only in game");
                return;
            }
            commandDelete:
            $deleteForm = new CustomForm(function(Player $player, ?array $data){
                if($data === null){
                    return;
                }
                $error = [];
                if(isset($data[0]) && $data[0] !== ""){
                    if(!$this->plugin->gameExists($data[0])){
                        $error[0] = TextFormat::RED . "Doesn't exist";
                    }
                } else {
                    $error[0] = TextFormat::RED . "Column can't be blank";
                }
                if(count($error) > 0){
                    $this->cachedCommandResponse[$player->getRawUniqueId()] = array('command' => 'delete', 'errors' => $error, 'values' => $data);
                    $this->sendFormCustom($player, $this->cachedFormResponse['delete'], 'delete');
                } else {
                    $this->plugin->deleteGame($data[0]);
                    $player->sendMessage(TextFormat::GREEN . "Game deleted");
                }
            });
            $this->sendFormCustom($sender, $deleteForm, 'delete');
            break;
            case "setlobby";
            if(!$sender instanceof Player){
                $sender->sendMessage(TextFormat::RED . "This command can be executed only in game");
                return;
            }
            $setlobbyForm = new CustomForm(function(Player $player, ?array $data){
                if($data === null){
                    return;
                }
                $error = [];
                if(isset($data[0]) && $data[0] !== ""){
                    if(!$this->plugin->gameExists($data[0])){
                        $error[0] = TextFormat::RED . "Doesn't exist";
                    }
                } else {
                    $error[0] = TextFormat::RED . "Column can't be blank";
                }
                if(isset($data[1]) && $data[1] !== ""){
                    if(!is_numeric($data[1])){
                        $error[1] = TextFormat::RED . "Must be numeric";
                    }
                } else {
                    $error[1] = TextFormat::RED . "Column can't be blank";
                }
                if(isset($data[2]) && $data[2] !== ""){
                    if(!is_numeric($data[2])){
                        $error[2] = TextFormat::RED . "Must be numeric";
                    }
                } else{ 
                    $error[2] = TextFormat::RED . "Column can't be blank";
                }
                if(isset($data[3]) && $data[3] !== ""){
                    if(!is_numeric($data[3])){
                        $error[3] = TextFormat::RED . "Must be numeric";
                    }
                } else {
                    $error[3] = TextFormat::RED . "Column can't be blank";
                }
                if(isset($data[4]) && $data[4] !== ""){
                    if(!$this->plugin->getServer()->isLevelLoaded($data[4])){
                        $error[4] = TextFormat::RED . "Level not loaded";
                    }
                } else {
                    $error[4] = TextFormat::RED . "Column can't be blank";
                }
                if(count($error) > 0){
                    $this->cachedCommandResponse[$player->getRawUniqueId()] = array('command' => 'setlobby', 'errors' => $error, 'values' => $data);
                    $this->sendFormCustom($player, $this->cachedFormResponse['setlobby'], 'setlobby');
                } else {
                    $level = $player->level;
                    $void_y = Level::Y_MAX;
                     foreach($level->getChunks() as $chunk){
                         for($x = 0; $x < 16; ++$x){
                             for($z = 0; $z < 16; ++$z){
                                 for($y = 0; $y < $void_y; ++$y){
                                      $block = $chunk->getBlockId($x, $y, $z);
                                      if($block !== Block::AIR){
                                           $void_y = $y;
                                          break;
                                      }
                                  }
                              }
                          }
                      }
                     --$void_y;
                    $this->plugin->setLobby($data[0], $data[1], $data[2], $data[3], $data[4], $void_y);
                    $player->sendMessage(TextFormat::GREEN . "Lobby set");
                }
            });
            $this->sendFormCustom($sender, $setlobbyForm, 'setlobby');
            break;
            case "setposition";
            if(!$sender instanceof Player){
                $sender->sendMessage(TextFormat::RED . "This command can be executed only in game");
                return;
            }
            $setpositionForm = new CustomForm(function(Player $player, ?array $data){
                if($data === null){
                    return;
                }
                $error = [];
                if(isset($data[0]) && $data[0] !== ""){
                    if(!$this->plugin->gameExists($data[0])){
                        $error[0] = TextFormat::RED . "Doesn't exist";
                    }
                } else {
                    $error[0] = TextFormat::RED . "Column can't be blank";
                }
                if(isset($data[1]) && $data[1] !== ""){
                    if(!$this->plugin->teamExists($data[0], strtolower(array_keys(BedWars::TEAMS)[$data[1]]))){
                        $error[1] = TextFormat::RED . "Doesn't exist";
                    }
                } else {
                    $error[1] = TextFormat::RED . "Column can't be blank";
                }
                if(isset($data[2]) && $data[2] !== ""){
                    if(!is_numeric($data[2])){
                        $error[2] = TextFormat::RED . "Must be numeric";
                    }
                } else {
                    $error[2] = TextFormat::RED . "Column can't be blank";
                }
                if(isset($data[3]) && $data[3] !== ""){
                    if(!is_numeric($data[3])){
                        $error[3] = TextFormat::RED . "Must be numeric";
                    }
                } else {
                    $error[3] = TextFormat::RED . "Column can't be blank";
                }
                if(isset($data[4]) && $data[4] !== ""){
                    if(!is_numeric($data[4])){
                        $error[4] = TextFormat::RED . "Must be numeric";
                    }
                } else {
                    $error[4] = TextFormat::RED . "Column can't be blank";
                }
                if(count($error) > 0){
                    $this->cachedCommandResponse[$player->getRawUniqueId()] = array('command' => 'setposition', 'errors' => $error, 'values' => $data);
                    $this->sendFormCustom($player, $this->cachedFormResponse['setposition'], 'setposition');
                } else {
                    $this->plugin->setTeamPosition($data[0], array_keys(BedWars::TEAMS)[$data[1]], $data[2], (int)$data[3], (int)$data[4], (int)$data[5], (float) $player->getYaw(), (float) $player->getPitch());
                    $player->sendMessage(TextFormat::GREEN . "Position set");
                }
            });
            $this->sendFormCustom($sender, $setpositionForm, 'setposition');
            break;
            case "setbed";
            if(!$sender instanceof Player){
                $sender->sendMessage(TextFormat::RED . "This command can be executed only in game");
                return;
            }
            $setbedForm = new CustomForm(function(Player $player, ?array $data){
                if($data === null){
                    return;
                }
                $error = [];
                if(isset($data[0]) && $data[0] !== ""){
                    if(!$this->plugin->gameExists($data[0])){
                        $error[0] = TextFormat::RED . "Doesn't exist";
                    }
                } else {
                    $error[0] = TextFormat::RED . "Column can't be blank";
                }
                if(isset($data[1]) && $data[1] !== ""){
                    if(!$this->plugin->teamExists($data[0], strtolower(array_keys(BedWars::TEAMS)[$data[1]]))){
                        $error[1] = TextFormat::RED . "Doesn't exist";
                    }
                } else {
                    $error[1] = TextFormat::RED . "Column can't be blank";
                }
                if(count($error) > 0){
                    $this->cachedCommandResponse[$player->getRawUniqueId()] = array('command' => 'setbed', 'errors' => $error, 'values' => $data);
                    $this->sendFormCustom($player, $this->cachedFormResponse['setbed'], 'setbed');
                } else {
                    $this->plugin->bedSetup[$player->getRawUniqueId()] = ['game' => $data[0], 'team' => array_keys(BedWars::TEAMS)[$data[1]], 'step' => 1];
                    $player->sendMessage(TextFormat::RED . "Break the bed");
                }
            });
            $this->sendFormCustom($sender, $setbedForm, 'setbed');
            break;
            case "setgenerator";
            $setgeneratorForm = new CustomForm(function(Player $player, ?array $data) {
                if($data === null){
                    return;
                }
                $error = [];
                if(isset($data[0]) && $data[0] !== ""){
                    if(!$this->plugin->gameExists($data[0])){
                        $error[0] = TextFormat::RED . "Doesn't exist";
                    }
                } else {
                    $error[0] = TextFormat::RED . "Column can't be blank";
                }
                $gens = array("Diamond", "Emerald", "Gold", "Iron");
                $generator = null;
                if(isset($data[1]) && $data[1] !== ""){
                    $generator = strtolower($gens[$data[1]]);
                } else {
                    $error[0] = TextFormat::RED . "Column can't be blank";
                }
                if(count($error) > 0){
                    $this->cachedCommandResponse[$player->getRawUniqueId()] = array('command' => 'setgenerator', 'errors' => $error, 'values' => $data);
                    $this->sendFormCustom($player, $this->cachedFormResponse['setgenerator'], 'setgenerator');
                } else if($generator !== null){
                    $arenaData = $this->plugin->getGameData($data[0]);
                    $arenaData['generatorInfo'][$data[0]][] = ['type' => $generator, 'position' => Utils::vectorToString("", $player), 'game'];
                    $this->plugin->writeGameData($data[0], $arenaData);
                    $player->sendMessage(TextFormat::GREEN . "Generator added ".$generator);
                }
            });
            $this->sendFormCustom($sender, $setgeneratorForm, 'setgenerator');
            break;
        }
    }
    
    /**
     * @return BedWars|Plugin $plugin
     */
    public function getPlugin(): Plugin {
        return $this->plugin;
    }
}
