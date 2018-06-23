<?php

namespace Sign;

//Base
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\Task;
//Utils
use pocketmine\utils\TextFormat as Color;
use pocketmine\utils\Config;
//EventListener
use pocketmine\event\Listener;
//PlayerEvents
use pocketmine\Player;
use pocketmine\event\player\PlayerHungerChangeEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerMoveEvent;
//ItemUndBlock
use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\item\enchantment\Enchantment;
//BlockEvents
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
//EntityEvents
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\entity\Effect;
//Level
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
//Sounds
use pocketmine\level\sound\AnvilFallSound;
use pocketmine\level\sound\BlazeShootSound;
use pocketmine\level\sound\GhastShootSound;
//Commands
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
//Tile
use pocketmine\tile\Sign;
use pocketmine\tile\Chest;
use pocketmine\tile\Tile;
//Nbt
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
//Inventar
use pocketmine\inventory\ChestInventory;
use pocketmine\inventory\Inventory;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;

class SignDate extends PluginBase implements Listener {
	
	public $prefix = Color::WHITE . "[" . Color::GOLD . "SignDate" . Color::WHITE . "] ";
	
	public $lb = Color::WHITE . "[" . Color::GREEN . "Lobby-2". Color::WHITE . "]";
	
	public $load = 1;
	
	public function onEnable() {
		
		@mkdir($this->getDataFolder());
		if (is_dir($this->getDataFolder()) !== true) {

            mkdir($this->getDataFolder());

        }

		if (is_dir($this->getDataFolder() . "/players") !== true) {
			
            mkdir($this->getDataFolder() . "/players");
            
        }

        $this->saveDefaultConfig();
        $this->reloadConfig();
        
        $this->getLogger()->info($this->prefix . Color::GREEN . "wurde erfolgreich aktiviert!");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getScheduler()->scheduleRepeatingTask(new SignUpdate($this), 10);
        
    }
    
    public function giveBoots(Player $player) {
    	
    	$playerfile = new Config("/Home/plugins/SignDate/players/" . $player->getName() . ".yml", Config::YAML);
        if ($playerfile->get("SpeedA") === true) {
        	
        	$player->getArmorInventory()->setBoots(Item::get(305, 0, 1));
            $eff = new EffectInstance(Effect::getEffect(Effect::SPEED) , 500 * 20 , 1 , false);
            $player->addEffect($eff);  
          
        }
        
        if ($playerfile->get("JumpA") === true) {
        	
        	$player->getArmorInventory()->setBoots(Item::get(305, 0, 1));
            $eff = new EffectInstance(Effect::getEffect(Effect::JUMP) , 500 * 20 , 1 , false);
            $player->addEffect($eff);
        
        }
    	
    }
    
    public function onInteract(PlayerInteractEvent $event) {
    	
    	$player = $event->getPlayer();
        $block = $event->getBlock();
        $tile = $player->getLevel()->getTile($block);
        if ($tile instanceof Sign) {
        	
        	$signtext = $tile->getText();
            if ($signtext[0] == $this->lb) {
            	
            	if ($signtext[1] === Color::GREEN . "Lobby-2") {
            	
            	    if ($signtext[2] == Color::GREEN . "Betreten") {
            	
            	        $playerfile = new Config("/Home/plugins/SignDate/players/" . $player->getName() . ".yml", Config::YAML);
                        $playerfile->set("Port", 19133);
                        $playerfile->save();
            	        $player->transfer("Syntoxien.tk", 19133);
            
                    } else if ($signtext[2] == Color::DARK_RED . "Voll") {
                    	
                    	$playerfile = new Config("/Home/plugins/SignDate/players/" . $player->getName() . ".yml", Config::YAML);
                        $playerfile->set("Port", 19133);
                        $playerfile->save();
            	        $player->transfer("Syntoxien.tk", 19133);
            
                    } else {
                    	
                    	$player->sendMessage(Color::RED . "Du kannst Lobby-2 nicht mehr betreten!");
                        
                    }
                    
                }
            	
            }
        	
        }
    	
    }
	
}

class SignUpdate extends Task {
	
	public function __construct($plugin) {
		
		$this->plugin = $plugin;
		$this->prefix = $this->plugin->prefix;
		$this->lb = $this->plugin->lb;
        
    }
    
    public function onRun($tick) {
    	
    	$level = $this->plugin->getServer()->getDefaultLevel();
        $tiles = $level->getTiles();
        $config = $this->plugin->getConfig();
        if ($this->plugin->load === 1) {
        	
        	$this->plugin->load = 2;
        	
        } else if ($this->plugin->load === 2) {
        	
        	$this->plugin->load = 3;
        	
        } else if ($this->plugin->load === 3) {
        	
        	$this->plugin->load = 1;
        	
        }
        
        foreach ($tiles as $t) {
        	
        	if ($t instanceof Sign) {
        	
        	    $level->loadChunk($t->getX(), $t->getZ());
        	    $text = $t->getText();
                if ($text[0] === "Lobby-2") {
                	
                	$t->setText($this->plugin->lb,
                    Color::GREEN . "Lobby-2",
                    Color::GREEN . "Betreten",
                    Color::WHITE . "[" . Color::RED . "0" . Color::GRAY . "/" . Color::RED . "25" . Color::WHITE . "]"        
                     );                                                             	                     
                	
                } else if ($text[0] === $this->plugin->lb) {
                	
                	if ($text[1] === Color::GREEN . "Lobby-2") {
                	
                	    $lb = new Config("/Home/plugins/Lobby-2/config.yml");
                        if ($lb->get("Voll") === true) {
                        	
                        	$t->setText($this->plugin->lb,
                            Color::GREEN . "Lobby-2",
                            Color::DARK_RED . "Voll",
                            Color::WHITE . "[" . Color::RED . $lb,>get("players") . Color::GRAY . "/" . Color::RED . "25" . Color::WHITE . "]"        
                             );
                        	
                        } else {
                        	
                        	if ($lb->get("reset") === true) {
                        	
                        	    if ($this->plugin->load === 1) {
                        	
                        	        $t->setText($this->plugin->lb,
                                    Color::GREEN . "Lobby-2",
                                    Color::RED . "Lade Lobby",
                                    Color::WHITE . "Ooo"
                                    );
                                    
                                } else if ($this->plugin->load === 2) {
                        	
                        	        $t->setText($this->plugin->lb,
                                    Color::GREEN . "Lobby-2",
                                    Color::RED . "Lade Lobby",
                                    Color::WHITE . "oOo"
                                    );
                                    
                                } else if ($this->plugin->load === 3) {
                        	
                        	        $t->setText($this->plugin->lb,
                                    Color::GREEN . "Lobby-2",
                                    Color::RED . "Lade Lobby",
                                    Color::WHITE . "ooO"
                                    );
                                    
                                }
                                
                            } else {
                        	
                        	if ($lb->get("players") === 25) {
                        	
                        	    $t->setText($this->plugin->lb,
                                Color::GREEN . "Lobby-2",
                                Color::DARK_RED . "Voll",
                                Color::WHITE . "[" . Color::RED . $lb->get("players") . Color::GRAY . "/" . Color::RED . "25" . Color::WHITE . "]"        
                                );
                                
                            } else {
                            	
                            	$t->setText($this->plugin->lb,
                                Color::DARK_RED . "Lobby-2",
                                Color::GREEN . "Betreten",
                                Color::WHITE . "[" . Color::RED . $lb->get("players") . Color::GRAY . "/" . Color::RED . "25" . Color::WHITE . "]"        
                                );   
                        	                                                 	
                            }
                            
                            }
                        	
                        }
                        
                    }
                	    
                }
                
            }
        	
        }
    	
    }
	
}  