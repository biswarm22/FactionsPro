<?php

namespace FactionsPro;

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\Player;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\utils\TextFormat;
use pocketmine\scheduler\PluginTask;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\utils\Config;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\block\BlockPlaceEvent;


class FactionListener implements Listener {
	
	public $plugin;
	
	public function __construct(FactionMain $pg) {
		$this->plugin = $pg;
	}
	
	public function factionChat(PlayerChatEvent $PCE) {
		
		$player = strtolower($PCE->getPlayer()->getName());
		//MOTD Check
		//TODO Use arrays instead of database for faster chatting?
		
		if($this->plugin->motdWaiting($player)) {
			if(time() - $this->plugin->getMOTDTime($player) > 30) {
				$PCE->getPlayer()->sendMessage($this->plugin->formatMessage("Timed out. Please use /f motd again."));
				$this->plugin->db->query("DELETE FROM motdrcv WHERE player='$player';");
				$PCE->setCancelled(true);
				return true;
			} else {
				$motd = $PCE->getMessage();
				$faction = $this->plugin->getPlayerFaction($player);
				$this->plugin->setMOTD($faction, $player, $motd);
				$PCE->setCancelled(true);
				$PCE->getPlayer()->sendMessage($this->plugin->formatMessage("Successfully updated faction message of the day!", true));
			}
			return true;
		}
		
		//Member
		if($this->plugin->isInFaction($PCE->getPlayer()->getName()) && $this->plugin->isMember($PCE->getPlayer()->getName())) {
			$message = $PCE->getMessage();
			$player = $PCE->getPlayer()->getName();
			$faction = $this->plugin->getPlayerFaction($player);
			
			$PCE->setFormat("[$faction] $player: $message");
			return true;
		}
		//Officer
		elseif($this->plugin->isInFaction($PCE->getPlayer()->getName()) && $this->plugin->isOfficer($PCE->getPlayer()->getName())) {
			$message = $PCE->getMessage();
			$player = $PCE->getPlayer()->getName();
			$faction = $this->plugin->getPlayerFaction($player);
			
			$PCE->setFormat("*[$faction] $player: $message");
			return true;
		}
		//Leader
		elseif($this->plugin->isInFaction($PCE->getPlayer()->getName()) && $this->plugin->isLeader($PCE->getPlayer()->getName())) {
			$message = $PCE->getMessage();
			$player = $PCE->getPlayer()->getName();
			$faction = $this->plugin->getPlayerFaction($player);
			$PCE->setFormat("**[$faction] $player: $message");
			return true;
		//Not in faction
		}else {
			$message = $PCE->getMessage();
			$player = $PCE->getPlayer()->getName();
			$PCE->setFormat("$player: $message");
		}
	}
	
	public function factionPVP(EntityDamageEvent $factionDamage) {
		if($factionDamage instanceof EntityDamageByEntityEvent) {
			if(!($factionDamage->getEntity() instanceof Player) or !($factionDamage->getDamager() instanceof Player)) {
				return true;
			}
			if(($this->plugin->isInFaction($factionDamage->getEntity()->getPlayer()->getName()) == false) or ($this->plugin->isInFaction($factionDamage->getDamager()->getPlayer()->getName()) == false) ) {
				return true;
			}
			if(($factionDamage->getEntity() instanceof Player) and ($factionDamage->getDamager() instanceof Player)) {
				$player1 = $factionDamage->getEntity()->getPlayer()->getName();
				$player2 = $factionDamage->getDamager()->getPlayer()->getName();
				if($this->plugin->sameFaction($player1, $player2) == true) {
			}
		}
	}
    public function factionBlockBreakProtect(BlockBreakEvent $event)
    {
        if ($this->plugin->isInPlot($event->getPlayer())) {
            if ($this->plugin->inOwnPlot($event->getPlayer())) {
                return true;
            } elseif ($event->getPlayer()->hasPermission("f.override")) {
                return true;
            } else
                $event->setCancelled(true);
            $event->getPlayer()->sendPopup("§cThis area is already claimed. - Run /f help");
            return true;
        }
    }


    public function factionBlockPlaceProtect(BlockPlaceEvent $event)
    {
        if ($this->plugin->isInPlot($event->getPlayer())) {
            if ($this->plugin->inOwnPlot($event->getPlayer())) {
                return true;
            } elseif ($event->getPlayer()->hasPermission("f.override")) {
                return true;
            } else
                $event->setCancelled(true);
            $event->getPlayer()->sendPopup("§cThis area is already claimed. - Run /f help");
            return true;
			}
		}
	}
