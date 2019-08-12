<?php

declare(strict_types=1);

namespace xenialdan\BedWars\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use xenialdan\BedWars\Loader;
use xenialdan\gameapi\API;
use xenialdan\gameapi\Game;

class BedwarsCommand extends PluginCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct("bw", $plugin);
        $this->setAliases(["bedwars"]);
        $this->setPermission("bedwars.command");
        $this->setDescription("§f[§4Bed§fWars]§6 Einstellung");
        $this->setUsage("/bw | /bw setup | /bw endsetup | /leave | /start | /bw stop | /bw status | /bw info");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        /** @var Player $sender */
        $return = $sender->hasPermission($this->getPermission());
        if (!$return) {
            $sender->sendMessage(TextFormat::RED . "§f[§4Bed§fWars] §6Du hast keine Berechtigung um diesen Befehl auszuführen");
            return true;
        }
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "§f[§4Bed§fWars] §6Du kannst diesen Befehl hier nicht ausführen!");
            return false;
        }
        try {
            $return = true;
            switch ($args[0] ?? "help") {
                case "setup":
                    {
                        if (!$sender->hasPermission("bedwars.command.setup")) {
                            $sender->sendMessage(TextFormat::RED . "§f[§4Bed§fWars] §cDu hast keine Berechtigung um diesen Befehl auszuführen");
                            return true;
                        }
                        /** @var Game $p */
                        $p = $this->getPlugin();
                        $p->setupArena($sender);
                        break;
                    }
                case "leave":
                    {
                        if (!$sender->hasPermission("bedwars.command.leave")) {
                            $sender->sendMessage(TextFormat::RED . "§f[§4Bed§fWars] §cDu hast keine Berechtigung um diesen Befehl auszuführen");
                            return true;
                        }
                        $arena = API::getArenaOfPlayer($sender);
                        if(is_null($arena) || !API::isArenaOf($this->getPlugin(), $arena->getLevel())){
                            /** @var Game $plugin */
                            $plugin = $this->getPlugin();
                            $sender->sendMessage(TextFormat::RED."Du bist in keiner Runde ". $plugin->getPrefix());
                            return true;
                        }
                        if (API::isPlaying($sender, $this->getPlugin())) $arena->removePlayer($sender);
                        break;
                    }
                case "endsetup":
                    {
                        if (!$sender->hasPermission("bedwars.command.endsetup")) {//TODO only when setup
                            $sender->sendMessage(TextFormat::RED . "§f[§4Bed§fWars] §cDu hast keine Berechtigung um diesen Befehl auszuführen");
                            return true;
                        }
                        /** @var Game $p */
                        $p = $this->getPlugin();
                        $p->endSetupArena($sender);
                        break;
                    }
                case "stop":
                    {
                        if (!$sender->hasPermission("bedwars.command.stop")) {
                            $sender->sendMessage(TextFormat::RED . "§f[§4Bed§fWars] §cDu hast keine Berechtigung um diesen Befehl auszuführen");
                            return true;
                        }
                        API::getArenaByLevel(Loader::getInstance(), $sender->getLevel())->stopArena();
                        break;
                    }
                case "forcestart":
                    {
                        if (!$sender->hasPermission("bedwars.command.forcestart")) {
                            $sender->sendMessage(TextFormat::RED . "§f[§4Bed§fWars] §cDu hast keine Berechtigung um diesen Befehl auszuführen");
                            return true;
                        }
                        $arena = API::getArenaOfPlayer($sender);
                        if(is_null($arena) || !API::isArenaOf($this->getPlugin(), $arena->getLevel())){
                            /** @var Game $plugin */
                            $plugin = $this->getPlugin();
                            $sender->sendMessage(TextFormat::RED."§f[§4Bed§fWars] §6Du bist in keiner Runde ". $plugin->getPrefix());
                            return true;
                        }
                        $arena->startTimer($arena->getOwningGame());
                        $arena->forcedStart = true;
                        $arena->setTimer(5);
                        $sender->getServer()->broadcastMessage("§f[§4Bed§fWars] §6Das Spiel wurde gestartet von " . $sender->getDisplayName(), $arena->getPlayers());
                        break;
                    }
                case "help":
                    {
                        if (!$sender->hasPermission("bedwars.command.help")) {
                            $sender->sendMessage(TextFormat::RED . "§f[§4Bed§fWars] §cDu hast keine Berechtigung um diesen Befehl auszuführen");
                            return true;
                        }
                        $sender->sendMessage($this->getUsage());
                        $return = true;
                        break;
                    }
                default:
                    {
                        $return = false;
                        throw new \InvalidArgumentException("Unknown argument supplied: " . $args[0]);
                    }
            }
        } catch (\Throwable $error) {
            $this->getPlugin()->getLogger()->logException($error);
            $return = false;
        } finally {
            return $return;
        }
    }
}
