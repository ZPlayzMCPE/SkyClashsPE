<?php

namespace PocketMiner\SkyWars;

use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\PluginTask;
use pocketmine\event\Listener;

use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat as TE;
use pocketmine\utils\Config;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\tile\Sign;
use pocketmine\level\Level;
use pocketmine\item\Item;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\tile\Chest;
use pocketmine\inventory\ChestInventory;
use pocketmine\event\player\PlayerQuitEvent;
use PocketMiner\SkyWars\ResetMap;
use pocketmine\level\sound\PopSound;
use pocketmine\level\sound\ExplodeSound;
use pocketmine\block\Air;

class SkyWars extends PluginBase implements Listener {

    public $prefix = TE::GRAY . "" . TE::GOLD . TE::BOLD . "Sky" . TE::GREEN . "Clash" . TE::RESET . TE::GRAY . "";
	public $mode = 0;
	public $arenas = array();
	public $currentLevel = "";
        public $op = "";
	
	public function onEnable()
	{
		  $this->getLogger()->info(TE::DARK_AQUA . "§6Sky§cClash§bPE");
                  
                $this->getServer()->getPluginManager()->registerEvents($this ,$this);
                $this->kit = $this->getServer()->getPluginManager()->getPlugin("KitsDoDo");
		@mkdir($this->getDataFolder());
                $config2 = new Config($this->getDataFolder() . "/rank.yml", Config::YAML);
		$config2->save();
		$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
		if($config->get("arenas")!=null)
		{
			$this->arenas = $config->get("arenas");
		}
		foreach($this->arenas as $lev)
		{
			$this->getServer()->loadLevel($lev);
		}
		$items = array(array(1,0,30),array(1,0,20),array(3,0,15),array(3,0,25),array(4,0,35),array(4,0,15),array(260,0,5),array(261,0,1),array(262,0,6),array(267,0,1),array(268,0,1),array(272,0,1),array(276,0,1),array(283,0,1),array(297,0,3),array(298,0,1),array(299,0,1),array(300,0,1),array(301,0,1),array(303,0,1),array(304,0,1),array(310,0,1),array(313,0,1),array(314,0,1),array(315,0,1),array(316,0,1),array(317,0,1),array(320,0,4),array(354,0,1),array(364,0,4),array(366,0,5),array(391,0,5));
		if($config->get("chestitems")==null)
		{
			$config->set("chestitems",$items);
		}
		$config->save();
                $slots = new Config($this->getDataFolder() . "/slots.yml", Config::YAML);
                $slots->save();
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new GameSender($this), 20);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new RefreshSigns($this), 10);
	}
        
        public function onDisable() {
            $config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
            $slots = new Config($this->getDataFolder() . "/slots.yml", Config::YAML);
            if($config->get("arenas")!=null)
            {
                    $this->arenas = $config->get("arenas");
            }
            foreach($this->arenas as $arena)
            {
                    $slots->set("slot1".$arena, 0);
                    $slots->set("slot2".$arena, 0);
                    $slots->set("slot3".$arena, 0);
                    $slots->set("slot4".$arena, 0);
                    $slots->set("slot5".$arena, 0);
                    $slots->set("slot6".$arena, 0);
                    $slots->set("slot7".$arena, 0);
                    $slots->set("slot8".$arena, 0);
                    $slots->set("slot9".$arena, 0);
                    $slots->set("slot10".$arena, 0);
                    $slots->set("slot11".$arena, 0);
                    $slots->set("slot12".$arena, 0);
                    $slots->save();
            }
        }
	
	public function onDeath(PlayerDeathEvent $event){
        $jugador = $event->getEntity();
        $mapa = $jugador->getLevel()->getFolderName();
        if(in_array($mapa,$this->arenas))
	{
            $event->setDeathMessage("");
                if($event->getEntity()->getLastDamageCause() instanceof EntityDamageByEntityEvent)
                {
                $asassin = $event->getEntity()->getLastDamageCause()->getDamager();
                if($asassin instanceof Player){
                foreach($jugador->getLevel()->getPlayers() as $pl){
                                $muerto = $jugador->getNameTag();
                                $asesino = $asassin->getNameTag();
				$pl->sendMessage(TE::RED . $muerto . TE::YELLOW . " assassinato da " . TE::GREEN . $asesino . TE::YELLOW.  ".");
			}
                }
                }
                $jugador->setNameTag($jugador->getName());
                $this->kit->clearall($jugador);
        }
        }
        
        public function entitychanger(EntityLevelChangeEvent $event) {
            $pl = $event->getEntity();
            $level = $pl->getLevel()->getFolderName();
            if($pl instanceof Player)
            {
                $lev = $event->getOrigin();
                if($lev instanceof Level && in_array($lev->getFolderName(),$this->arenas))
		{
                $pl->setNameTag($pl->getName());
                $pl->getInventory()->clearAll();
                $this->kit->clearall($pl);
                $slots = new Config($this->getDataFolder() . "/slots.yml", Config::YAML);
                if($slots->get("slot1".$level)==$pl->getName())
                {
                    $slots->set("slot1".$level, 0);
                }
                if($slots->get("slot2".$level)==$pl->getName())
                {
                    $slots->set("slot2".$level, 0);
                }
                if($slots->get("slot3".$level)==$pl->getName())
                {
                    $slots->set("slot3".$level, 0);
                }
                if($slots->get("slot4".$level)==$pl->getName())
                {
                    $slots->set("slot4".$level, 0);
                }
                if($slots->get("slot5".$level)==$pl->getName())
                {
                    $slots->set("slot5".$level, 0);
                }
                if($slots->get("slot6".$level)==$pl->getName())
                {
                    $slots->set("slot6".$level, 0);
                }
                if($slots->get("slot7".$level)==$pl->getName())
                {
                    $slots->set("slot7".$level, 0);
                }
                if($slots->get("slot8".$level)==$pl->getName())
                {
                    $slots->set("slot8".$level, 0);
                }
                if($slots->get("slot9".$level)==$pl->getName())
                {
                    $slots->set("slot9".$level, 0);
                }
                if($slots->get("slot10".$level)==$pl->getName())
                {
                    $slots->set("slot10".$level, 0);
                }
                if($slots->get("slot11".$level)==$pl->getName())
                {
                    $slots->set("slot11".$level, 0);
                }
                if($slots->get("slot12".$level)==$pl->getName())
                {
                    $slots->set("slot12".$level, 0);
                }
                $slots->save();
                }
            }
        }
	
	public function onMove(PlayerMoveEvent $event)
	{
		$player = $event->getPlayer();
		$level = $player->getLevel()->getFolderName();
		if(in_array($level,$this->arenas))
		{
			$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
			$sofar = $config->get($level . "StartTime");
			if($sofar > 0)
			{
				$to = clone $event->getFrom();
				$to->yaw = $event->getTo()->yaw;
				$to->pitch = $event->getTo()->pitch;
				$event->setTo($to);
			}
		}
	}
	
	public function onLog(PlayerLoginEvent $event)
	{
		$player = $event->getPlayer();
                if(in_array($player->getLevel()->getFolderName(),$this->arenas))
		{
		$player->getInventory()->clearAll();
		$spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
		$this->getServer()->getDefaultLevel()->loadChunk($spawn->getFloorX(), $spawn->getFloorZ());
		$player->teleport($spawn,0,0);
                }
	}
        
        public function onQuit(PlayerQuitEvent $event)
        {
            $pl = $event->getPlayer();
            $level = $pl->getLevel()->getFolderName();
            if(in_array($level,$this->arenas))
            {
                $pl->removeAllEffects();
                $pl->getInventory()->clearAll();
                $slots = new Config($this->getDataFolder() . "/slots.yml", Config::YAML);
                $pl->setNameTag($pl->getName());
                if($slots->get("slot1".$level)==$pl->getName())
                {
                    $slots->set("slot1".$level, 0);
                }
                if($slots->get("slot2".$level)==$pl->getName())
                {
                    $slots->set("slot2".$level, 0);
                }
                if($slots->get("slot3".$level)==$pl->getName())
                {
                    $slots->set("slot3".$level, 0);
                }
                if($slots->get("slot4".$level)==$pl->getName())
                {
                    $slots->set("slot4".$level, 0);
                }
                if($slots->get("slot5".$level)==$pl->getName())
                {
                    $slots->set("slot5".$level, 0);
                }
                if($slots->get("slot6".$level)==$pl->getName())
                {
                    $slots->set("slot6".$level, 0);
                }
                if($slots->get("slot7".$level)==$pl->getName())
                {
                    $slots->set("slot7".$level, 0);
                }
                if($slots->get("slot8".$level)==$pl->getName())
                {
                    $slots->set("slot8".$level, 0);
                }
                if($slots->get("slot9".$level)==$pl->getName())
                {
                    $slots->set("slot9".$level, 0);
                }
                if($slots->get("slot10".$level)==$pl->getName())
                {
                    $slots->set("slot10".$level, 0);
                }
                if($slots->get("slot11".$level)==$pl->getName())
                {
                    $slots->set("slot11".$level, 0);
                }
                if($slots->get("slot12".$level)==$pl->getName())
                {
                    $slots->set("slot12".$level, 0);
                }
                $slots->save();
            }
        }
	
	public function onBlockBreak(BlockBreakEvent $event)
	{
		$player = $event->getPlayer();
		$level = $player->getLevel()->getFolderName();
		if(in_array($level,$this->arenas))
		{
                        $config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
                        if($config->get($level . "PlayTime") != null)
                        {
                                if($config->get($level . "PlayTime") > 779)
                                {
                                        $event->setCancelled(true);
                                }
                        }
		}
	}
	
	public function onBlockPlace(BlockPlaceEvent $event)
	{
		$player = $event->getPlayer();
		$level = $player->getLevel()->getFolderName();
		if(in_array($level,$this->arenas))
		{
			$event->setCancelled(false);
		}
	}
	
	public function onDamage(EntityDamageEvent $event)
	{
		if($event instanceof EntityDamageByEntityEvent)
		{
			$player = $event->getEntity();
			$damager = $event->getDamager();
			if($player instanceof Player)
			{
				if($damager instanceof Player)
				{
					$level = $player->getLevel()->getFolderName();
					$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
					if($config->get($level . "PlayTime") != null)
					{
						if($config->get($level . "PlayTime") > 750)
						{
							$event->setCancelled();
						}
					}
				}
			}
		}
	}
	
	public function onCommand(CommandSender $player, Command $cmd, $label, array $args) {
        switch($cmd->getName()){
			case "sc":
				if($player->isOp())
				{
					if(!empty($args[0]))
					{
						if($args[0]=="crea")
						{
							if(!empty($args[1]))
							{
								if(file_exists($this->getServer()->getDataPath() . "/worlds/" . $args[1]))
								{
									$this->getServer()->loadLevel($args[1]);
									$this->getServer()->getLevelByName($args[1])->loadChunk($this->getServer()->getLevelByName($args[1])->getSafeSpawn()->getFloorX(), $this->getServer()->getLevelByName($args[1])->getSafeSpawn()->getFloorZ());
									array_push($this->arenas,$args[1]);
                                                                        $this->op = $player->getName();
									$this->currentLevel = $args[1];
									$this->mode = 1;
									$player->sendMessage("Tocca i punti per gli spawn");
									$player->setGamemode(1);
									$player->teleport($this->getServer()->getLevelByName($args[1])->getSafeSpawn(),0,0);
                                                                        $name = $args[1];
                                                                        $this->zipper($player, $name);
								}
								else
								{
									$player->sendMessage("Errore, mondo sconosciuto");
								}
							}
							else
							{
								$player->sendMessage("Errore nel comando");
							}
						}
						else
						{
							$player->sendMessage("Comando invalido");
						}
					}
					else
					{
					 $player->sendMessage("§aComandi SkyCLash");
                                         $player->sendMessage("§d/sc crea [mondo]: Crea un arena SkyClash");
                                         $player->sendMessage("§d/ranksc [rank] [giocatore]: ranks in beta");
                                         $player->sendMessage("§d/sctarta: inizia la partita");
					}
				}
				else
				{
				}
			return true;
                        
                        case "scstarta":
                            if($player->isOp())
				{
                                if(!empty($args[0]))
					{
                                        $config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
                                        if($config->get($args[0] . "StartTime") != null)
                                        {
                                        $config->set($args[0] . "StartTime", 10);
                                        $config->save();
                                        $player->sendMessage($this->prefix . "§aIniziando in 10...");
                                        }
                                        }
                                        else
                                        {
                                            $level = $player->getLevel()->getFolderName();
                                            $config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
                                            if($config->get($level . "StartTime") != null)
                                            {
                                            $config->set($level . "StartTime", 10);
                                            $config->save();
                                            $player->sendMessage($this->prefix . "§aIniziando in 10...");
                                            }
                                        }
                                }
                                return true;
	}
        }
	
	public function onInteract(PlayerInteractEvent $event)
	{
		$player = $event->getPlayer();
		$block = $event->getBlock();
		$tile = $player->getLevel()->getTile($block);
		
		if($tile instanceof Sign) 
		{
			if($this->mode==26 && $this->op==$player->getName())
			{
				$tile->setText(TE::AQUA . "§aUnisciti",TE::YELLOW  . "0 / 12","§f" . $this->currentLevel,$this->prefix);
				$this->refreshArenas();
				$this->currentLevel = "";
				$this->mode = 0;
				$player->sendMessage("Arena Registrata");
			}
			else
			{
				$text = $tile->getText();
				if($text[3] == $this->prefix)
				{
					if($text[0]==TE::AQUA . "§aEntra")
					{
						$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
                                                $slots = new Config($this->getDataFolder() . "/slots.yml", Config::YAML);
                                                $namemap = str_replace("§f", "", $text[2]);
						$level = $this->getServer()->getLevelByName($namemap);
                                                if($slots->get("slot1".$namemap)==null)
                                                {
                                                        $thespawn = $config->get($namemap . "Spawn1");
                                                        $slots->set("slot1".$namemap, $player->getName());
                                                        $slots->save();
                                                }
                                                elseif($slots->get("slot2".$namemap)==null)
                                                {
                                                        $thespawn = $config->get($namemap . "Spawn2");
                                                        $slots->set("slot2".$namemap, $player->getName());
                                                        $slots->save();
                                                }
                                                elseif($slots->get("slot3".$namemap)==null)
                                                {
                                                        $thespawn = $config->get($namemap . "Spawn3");
                                                        $slots->set("slot3".$namemap, $player->getName());
                                                        $slots->save();
                                                }
                                                elseif($slots->get("slot4".$namemap)==null)
                                                {
                                                        $thespawn = $config->get($namemap . "Spawn4");
                                                        $slots->set("slot4".$namemap, $player->getName());
                                                        $slots->save();
                                                }
                                                elseif($slots->get("slot5".$namemap)==null)
                                                {
                                                        $thespawn = $config->get($namemap . "Spawn5");
                                                        $slots->set("slot5".$namemap, $player->getName());
                                                        $slots->save();
                                                }
                                                elseif($slots->get("slot6".$namemap)==null)
                                                {
                                                        $thespawn = $config->get($namemap . "Spawn6");
                                                        $slots->set("slot6".$namemap, $player->getName());
                                                        $slots->save();
                                                }
                                                elseif($slots->get("slot7".$namemap)==null)
                                                {
                                                        $thespawn = $config->get($namemap . "Spawn7");
                                                        $slots->set("slot7".$namemap, $player->getName());
                                                        $slots->save();
                                                }
                                                elseif($slots->get("slot8".$namemap)==null)
                                                {
                                                        $thespawn = $config->get($namemap . "Spawn8");
                                                        $slots->set("slot8".$namemap, $player->getName());
                                                        $slots->save();
                                                }
                                                elseif($slots->get("slot9".$namemap)==null)
                                                {
                                                        $thespawn = $config->get($namemap . "Spawn9");
                                                        $slots->set("slot9".$namemap, $player->getName());
                                                        $slots->save();
                                                }
                                                elseif($slots->get("slot10".$namemap)==null)
                                                {
                                                        $thespawn = $config->get($namemap . "Spawn10");
                                                        $slots->set("slot10".$namemap, $player->getName());
                                                        $slots->save();
                                                }
                                                elseif($slots->get("slot11".$namemap)==null)
                                                {
                                                        $thespawn = $config->get($namemap . "Spawn11");
                                                        $slots->set("slot11".$namemap, $player->getName());
                                                        $slots->save();
                                                }
                                                elseif($slots->get("slot12".$namemap)==null)
                                                {
                                                        $thespawn = $config->get($namemap . "Spawn12");
                                                        $slots->set("slot12".$namemap, $player->getName());
                                                        $slots->save();
                                                }
                                                else
                                                {
                                                    $player->sendMessage($this->prefix."Non ci sono Slots");
                                                    goto sinslots;
                                                }
                                                $player->sendMessage("               §eEntra in §6Sky§cClash");
                                                foreach($level->getPlayers() as $playersinarena)
                                                {
                                                $playersinarena->sendMessage($player->getNameTag() .TE::AQUA."§a> §e è entrato in partita");
                                                }
						$spawn = new Position($thespawn[0]+0.5,$thespawn[1],$thespawn[2]+0.5,$level);
						$level->loadChunk($spawn->getFloorX(), $spawn->getFloorZ());
						$player->teleport($spawn,0,0);
						$player->getInventory()->clearAll();
                                                $player->removeAllEffects();
                                                $player->setMaxHealth(20);
                                                $player->setHealth(20);
                                                $player->setFood(20);
                                                $player->setNameTag("§o§a".$player->getNameTag());
                                                $this->kit->setkit($player);
                                                sinslots:
					}
					else
					{
                                            $player->sendMessage($this->prefix .TE::RED."Non puoi entrare");
					}
				}
			}
		}
		elseif($this->mode>=1&&$this->mode<=11 && $this->op==$player->getName())
		{
			$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
			$config->set($this->currentLevel . "Spawn" . $this->mode, array($block->getX(),$block->getY()+1,$block->getZ()));
			$player->sendMessage($this->prefix . "Spawn " . $this->mode . " è stato registrato!");
			$this->mode++;
			$config->save();
		}
		elseif($this->mode==12 && $this->op==$player->getName())
		{
			$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
			$config->set($this->currentLevel . "Spawn" . $this->mode, array($block->getX(),$block->getY()+1,$block->getZ()));
			$player->sendMessage($this->prefix . "Spawn " . $this->mode . " hè stato registrato!");
                        $config->set($this->currentLevel . "inizio", 0);
			$config->set("arenas",$this->arenas);
			$player->sendMessage("Tocca un cartello per registrare la mappa skyclash");
			$spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
			$this->getServer()->getDefaultLevel()->loadChunk($spawn->getFloorX(), $spawn->getFloorZ());
			$player->teleport($spawn,0,0);
			$config->save();
			$this->mode=26;
		}
	}
	
	public function refreshArenas()
	{
		$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
		$config->set("arenas",$this->arenas);
		foreach($this->arenas as $arena)
		{
			$config->set($arena . "PlayTime", 780);
			$config->set($arena . "StartTime", 30);
		}
		$config->save();
	}
        
        public function zipper($player, $name)
        {
        $path = realpath($player->getServer()->getDataPath() . 'worlds/' . $name);
				$zip = new \ZipArchive;
				@mkdir($this->getDataFolder() . 'arenas/', 0755);
				$zip->open($this->getDataFolder() . 'arenas/' . $name . '.zip', $zip::CREATE | $zip::OVERWRITE);
				$files = new \RecursiveIteratorIterator(
					new \RecursiveDirectoryIterator($path),
					\RecursiveIteratorIterator::LEAVES_ONLY
				);
                                foreach ($files as $datos) {
					if (!$datos->isDir()) {
						$relativePath = $name . '/' . substr($datos, strlen($path) + 1);
						$zip->addFile($datos, $relativePath);
					}
				}
				$zip->close();
				$player->getServer()->loadLevel($name);
				unset($zip, $path, $files);
        }
}

class RefreshSigns extends PluginTask {
    public $prefix = TE::GRAY . "" . TE::GOLD . TE::BOLD . "Sky" . TE::GREEN . "Clash" . TE::RESET . TE::GRAY . "";
	public function __construct($plugin)
	{
		$this->plugin = $plugin;
		parent::__construct($plugin);
	}
  
	public function onRun($tick)
	{
		$allplayers = $this->plugin->getServer()->getOnlinePlayers();
		$level = $this->plugin->getServer()->getDefaultLevel();
		$tiles = $level->getTiles();
		foreach($tiles as $t) {
			if($t instanceof Sign) {	
				$text = $t->getText();
				if($text[3]==$this->prefix)
				{
					$aop = 0;
                                        $namemap = str_replace("§f", "", $text[2]);
					foreach($allplayers as $player){if($player->getLevel()->getFolderName()==$namemap){$aop=$aop+1;}}
					$ingame = TE::AQUA . "§aUnisciti";
					$config = new Config($this->plugin->getDataFolder() . "/config.yml", Config::YAML);
					if($config->get($namemap . "PlayTime")!=780)
					{
						$ingame = TE::DARK_PURPLE . "In gioco";
					}
					elseif($aop>=12)
					{
						$ingame = TE::GOLD . "Pieno";
					}
					$t->setText($ingame,TE::YELLOW  . $aop . " / 12",$text[2],$this->prefix);
				}
			}
		}
	}
}

class GameSender extends PluginTask {
    public $prefix = TE::GRAY . "" . TE::GOLD . TE::BOLD . "Sky" . TE::GREEN . "Clash" . TE::RESET . TE::GRAY . "";
	public function __construct($plugin)
	{
		$this->plugin = $plugin;
		parent::__construct($plugin);
	}
        
        public function getResetmap() {
        Return new ResetMap($this);
        }
  
	public function onRun($tick)
	{
		$config = new Config($this->plugin->getDataFolder() . "/config.yml", Config::YAML);
		$arenas = $config->get("arenas");
		if(!empty($arenas))
		{
			foreach($arenas as $arena)
			{
				$time = $config->get($arena . "PlayTime");
				$timeToStart = $config->get($arena . "StartTime");
				$levelArena = $this->plugin->getServer()->getLevelByName($arena);
				if($levelArena instanceof Level)
				{
					$playersArena = $levelArena->getPlayers();
					if(count($playersArena)==0)
					{
						$config->set($arena . "PlayTime", 780);
						$config->set($arena . "StartTime", 30);
					}
					else
					{
                                            if(count($playersArena)>=2)
                                            {
                                                $config->set($arena . "inizio", 1);
                                                $config->save();
                                            }
                                            if($config->get($arena . "inizio")==1)
                                            {
							if($timeToStart>0)
							{
								$timeToStart--;
								foreach($playersArena as $pl)
								{
									$pl->sendPopup(TE::GREEN . $timeToStart . " secondi per incominciare" .TE::RESET);
                                                                        if($timeToStart<=5)
                                                                        {
                                                                        $levelArena->addSound(new PopSound($pl));
                                                                        }
								}
                                                                if($timeToStart==89)
                                                                {
                                                                    $levelArena->setTime(7000);
                                                                    $levelArena->stopTime();
                                                                }
								if($timeToStart<=0)
								{
									$this->refillChests($levelArena);
                                                                        foreach($playersArena as $pl)
                                                                        {
                                                                            $pl->getLevel()->setBlock($pl->add(0, -1, 0), new Air());
                                                                            $levelArena->addSound(new ExplodeSound($pl));
                                                                        }
								}
								$config->set($arena . "StartTime", $timeToStart);
							}
							else
							{
								$colors = array();
                                                                foreach($playersArena as $pl)
                                                                {
                                                                    if(strpos($pl->getNameTag(), "§o§a") !== false)
                                                                    {
                                                                    array_push($colors, $pl->getNameTag());
                                                                    }
                                                                }
                                                                $jog = count($colors);
                                                                if(($jog>=2))
                                                                {
                                                                    foreach($playersArena as $pl)
                                                                        {
                                                                        $pl->sendPopup(TE::GOLD.$jog." ".TE::AQUA."Giocatori restanti".TE::RESET);
                                                                        }
                                                                }
                                                                if($jog==1)
                                                                {
                                                                    $name = implode(".",$colors);
                                                                    $this->plugin->getServer()->broadcastMessage($this->prefix .TE::WHITE.$name." ".TE::GREEN."Ha vinto la partita in ".TE::AQUA.$arena);
                                                                    foreach ($playersArena as $pl)
                                                                    {
                                                                    $pl->teleport($this->plugin->getServer()->getDefaultLevel()->getSafeSpawn(),0,0);
                                                                    $pl->getInventory()->clearAll();
                                                                    $pl->removeAllEffects();
                                                                    $pl->setHealth(20);
                                                                    $pl->setFood(20);
                                                                    $pl->setNameTag($pl->getName());
                                                                    $this->plugin->kit->clearall($pl);
                                                                    $this->getResetmap()->reload($levelArena);
                                                                    }
                                                                    $config->set($arena . "PlayTime", 780);
                                                                    $config->set($arena . "StartTime", 30);
                                                                    $config->set($arena . "inicio", 0);
                                                                }
								$time--;
								if($time == 779)
								{
                                                                        $slots = new Config($this->plugin->getDataFolder() . "/slots.yml", Config::YAML);
                                                                        $slots->set("slot1".$arena, 0);
                                                                        $slots->set("slot2".$arena, 0);
                                                                        $slots->set("slot3".$arena, 0);
                                                                        $slots->set("slot4".$arena, 0);
                                                                        $slots->set("slot5".$arena, 0);
                                                                        $slots->set("slot6".$arena, 0);
                                                                        $slots->set("slot7".$arena, 0);
                                                                        $slots->set("slot8".$arena, 0);
                                                                        $slots->set("slot9".$arena, 0);
                                                                        $slots->set("slot10".$arena, 0);
                                                                        $slots->set("slot11".$arena, 0);
                                                                        $slots->set("slot12".$arena, 0);
                                                                        $slots->save();
									foreach($playersArena as $pl)
									{
                                                                            $pl->sendMessage("§e>--------------SkyClash-----------------");
                                                                            $pl->sendMessage("§e>§cSkyClash by GeoZDev!");
                                                                            $pl->sendMessage("§e>§fStai giocando su: §b" . $arena);
                                                                            $pl->sendMessage("§e>§bHai §a30 §bsecondi di invincibilità");
                                                                            $pl->sendMessage("§e>--------------SkyClash-----------------");
                                                                            $levelArena->addSound(new PopSound($pl));
									}
								}
                                                                if($time == 765)
								{
									foreach($playersArena as $pl)
									{
                                                                            $pl->sendMessage("§e>§bRimangono §a15 §bsecondi di invincibilità");
                                                                            $levelArena->addSound(new PopSound($pl));
									}
								}
								if($time == 750)
								{
									foreach($playersArena as $pl)
									{
                                                                            $pl->sendMessage("§e>§bNon sei più invincibile");
                                                                            $levelArena->addSound(new ExplodeSound($pl));
									}
								}
                                                                if($time == 550)
								{
									foreach($playersArena as $pl)
									{
                                                                                $pl->sendMessage("§e>§bGrazie per aver giocato!");
									}                                                     $levelArena->AddSound(new ExplodeSound($pl));
								}
                                                                if($time == 480)
								{
									foreach($playersArena as $pl)
									{
                                                                                $pl->sendMessage("§b>§aLe ceste sono state riempite!");
                                                                                $levelArena->addSound(new PopSound($pl));
									}
									$this->refillChests($levelArena);
									
								}
								if($time>=300)
								{
								$time2 = $time - 180;
								$minutes = $time2 / 60;
								}
								else
								{
									$minutes = $time / 60;
									if(is_int($minutes) && $minutes>0)
									{
										foreach($playersArena as $pl)
										{
											$pl->sendMessage($this->prefix .TE::YELLOW. $minutes . " " .TE::GREEN. "minuti restanti");
										}
									}
									elseif($time == 30 || $time == 15 || $time == 10 || $time ==5 || $time ==4 || $time ==3 || $time ==2 || $time ==1)
									{
										foreach($playersArena as $pl)
										{
											$pl->sendMessage($this->prefix .TE::YELLOW. $time . " " .TE::GREEN. "secondi restanti");
										}
									}
									if($time <= 0)
									{
                                                                            $this->plugin->getServer()->broadcastMessage($this->prefix .TE::GREEN."Nessun vincitore in ".TE::AQUA.$arena);
                                                                            foreach($playersArena as $pl)
                                                                            {
                                                                                    $pl->teleport($this->plugin->getServer()->getDefaultLevel()->getSafeSpawn(),0,0);
                                                                                    $pl->getInventory()->clearAll();
                                                                                    $pl->removeAllEffects();
                                                                                    $pl->setFood(20);
                                                                                    $pl->setHealth(20);
                                                                                    $pl->setNameTag($pl->getName());
                                                                                    $this->plugin->kit->clearall($pl);
                                                                                    $this->getResetmap()->reload($levelArena);
                                                                            }
                                                                            $config->set($arena . "inicio", 0);
                                                                            $time = 780;
									}
								}
								$config->set($arena . "PlayTime", $time);
							}
						}
						else
						{
                                                    foreach($playersArena as $pl)
                                                    {
                                                            $pl->sendPopup(TE::DARK_AQUA ."Servono altri giocatori".TE::RESET);
                                                    }
                                                    $config->set($arena . "PlayTime", 780);
                                                    $config->set($arena . "StartTime", 30);
						}
					}
				}
			}
		}
		$config->save();
	}
	
	public function refillChests(Level $level)
	{
		$config = new Config($this->plugin->getDataFolder() . "/config.yml", Config::YAML);
		$tiles = $level->getTiles();
		foreach($tiles as $t) {
			if($t instanceof Chest) 
			{
				$chest = $t;
				$chest->getInventory()->clearAll();
				if($chest->getInventory() instanceof ChestInventory)
				{
					for($i=0;$i<=26;$i++)
					{
						$rand = rand(1,3);
						if($rand==1)
						{
							$k = array_rand($config->get("chestitems"));
							$v = $config->get("chestitems")[$k];
							$chest->getInventory()->setItem($i, Item::get($v[0],$v[1],$v[2]));
						}
					}									
				}
			}
		}
	}
}