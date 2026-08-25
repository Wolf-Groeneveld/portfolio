import asyncio
import random
import sys

import pygame

from .config import (
    SCREEN_WIDTH,
    SCREEN_HEIGHT,
    FIELD_WIDTH,
    FPS,
    STARTING_LIVES,
    STARTING_GOLD,
    PREP_FIRST,
    PREP_BETWEEN,
    EARLY_BONUS_PER_SECOND,
    COLOR_BG,
    COLOR_TX_HI,
    COLOR_ACCENT,
    COLOR_GO,
    COLOR_BAD,
)
from .level import Level, cell_center
from .towers import Tower
from .enemies import Enemy, HUSK, STRIDER, JUGGERNAUT
from .waves import WaveRunner, WAVES
from . import ui
from . import story


class Game:
    def __init__(self, fullscreen=True):
        pygame.init()
        pygame.display.set_caption("Sylensial's Nightmare - Part One")
        # In de browser (pygbag/WebAssembly) bestaat fullscreen niet; het canvas bepaalt de grootte.
        if sys.platform == "emscripten":
            fullscreen = False
        if fullscreen:
            self.display = pygame.display.set_mode((0, 0), pygame.FULLSCREEN)
        else:
            self.display = pygame.display.set_mode((SCREEN_WIDTH, SCREEN_HEIGHT))
        self.screen = pygame.Surface((SCREEN_WIDTH, SCREEN_HEIGHT))
        self._compute_scale()
        self.clock = pygame.time.Clock()
        self.fonts = self._load_fonts()
        self.level = Level()
        self.path = self.level.points
        self.hud = ui.HUD(self.fonts)
        self.total_waves = len(WAVES)
        self.time = 0.0
        self.pointer = (0, 0)
        self.reset()
        self._init_title()
        self.state = "title"

    def _compute_scale(self):
        dw, dh = self.display.get_size()
        self.scale = min(dw / SCREEN_WIDTH, dh / SCREEN_HEIGHT)
        self.scaled_size = (int(SCREEN_WIDTH * self.scale), int(SCREEN_HEIGHT * self.scale))
        self.offset = ((dw - self.scaled_size[0]) // 2, (dh - self.scaled_size[1]) // 2)

    def _load_fonts(self):
        serif = "georgia,timesnewroman,serif"
        sans = "segoeui,arial,sans"
        return {
            "mega": pygame.font.SysFont(serif, 56, bold=True),
            "big": pygame.font.SysFont(serif, 30, bold=True),
            "display": pygame.font.SysFont(serif, 44, bold=True),
            "banner": pygame.font.SysFont(serif, 18, bold=True),
            "title": pygame.font.SysFont(sans, 26, bold=True),
            "h1": pygame.font.SysFont(sans, 24, bold=True),
            "h2": pygame.font.SysFont(sans, 16, bold=True),
            "brand": pygame.font.SysFont(sans, 19, bold=True),
            "stat": pygame.font.SysFont(sans, 28, bold=True),
            "body": pygame.font.SysFont(sans, 17),
            "small": pygame.font.SysFont(sans, 13),
            "label": pygame.font.SysFont(sans, 11, bold=True),
            "micro": pygame.font.SysFont(sans, 11),
        }

    def reset(self):
        self.lives = STARTING_LIVES
        self.gold = STARTING_GOLD
        self.wave_index = 0
        self.wave_active = False
        self.enemies = []
        self.towers = []
        self.projectiles = []
        self.runner = WaveRunner(self.path)
        self.selected_tool = None
        self.selected_tower = None
        self.banner = story.ACT_LINES[0]
        self.prep_timer = PREP_FIRST
        self.gamble_open = False
        self.gamble_phase = "choose"
        self.gamble_stake = min(50, self.gold)
        self.gamble_mult = None
        self.gamble_chance = 0.0
        self.gamble_delta = 0
        self.gamble_reel = "?"
        self.gamble_reel_t = 0.0
        self.gamble_spin_t = 0.0
        self.state = "prep"

    def _init_title(self):
        self.title_enemies = []
        self.title_spawn_t = 0.0
        self.embers = [self._spawn_ember(True) for _ in range(30)]

    def _spawn_ember(self, initial):
        life = random.uniform(1.8, 4.5)
        return {
            "x": random.uniform(0, SCREEN_WIDTH),
            "y": random.uniform(0, SCREEN_HEIGHT) if initial else SCREEN_HEIGHT + random.uniform(0, 30),
            "vx": random.uniform(-10, 10),
            "vy": random.uniform(-55, -22),
            "life": life,
            "max": life,
        }

    async def run(self):
        running = True
        while running:
            dt = min(self.clock.tick(FPS) / 1000.0, 0.05)
            for event in pygame.event.get():
                if event.type == pygame.QUIT:
                    running = False
                elif event.type == pygame.KEYDOWN:
                    if not self._on_key(event.key):
                        running = False
                elif event.type == pygame.MOUSEBUTTONDOWN:
                    self._on_mouse(self.to_design(event.pos), event.button)
            self.update(dt)
            self.draw()
            # Geeft de browser elke frame de kans om te renderen (vereist voor pygbag).
            await asyncio.sleep(0)
        pygame.quit()

    def to_design(self, pos):
        x = (pos[0] - self.offset[0]) / self.scale
        y = (pos[1] - self.offset[1]) / self.scale
        return (int(x), int(y))

    def spawns_pending(self):
        if not self.runner.running:
            return 0
        return max(0, len(self.runner.schedule) - self.runner.index)

    def tower_at(self, cell):
        for tower in self.towers:
            if tower.cell == cell:
                return tower
        return None

    def _on_key(self, key):
        if key == pygame.K_ESCAPE:
            if self.gamble_open:
                self.gamble_open = False
                return True
            if self.selected_tool or self.selected_tower:
                self.selected_tool = None
                self.selected_tower = None
                return True
            return False
        if self.state == "title":
            if key in (pygame.K_RETURN, pygame.K_KP_ENTER, pygame.K_SPACE):
                self._begin()
            return True
        if self.state in ("victory", "defeat"):
            if key in (pygame.K_RETURN, pygame.K_KP_ENTER, pygame.K_SPACE):
                self.reset()
            return True
        if key == pygame.K_g:
            self._toggle_gamble()
            return True
        if key == pygame.K_SPACE and self.state == "prep":
            self._call_wave()
        return True

    def _on_mouse(self, pos, button):
        if self.state == "title":
            self._begin()
            return
        if self.state in ("victory", "defeat"):
            self.reset()
            return
        if self.gamble_open:
            if button == 1:
                self._click_gamble(pos)
            return
        if button == 3:
            self.selected_tool = None
            self.selected_tower = None
            return
        if button != 1:
            return
        if pos[0] >= FIELD_WIDTH:
            self._click_panel(pos)
        else:
            self._click_field(pos)

    def _begin(self):
        self.reset()

    def _toggle_gamble(self):
        if self.state in ("title", "victory", "defeat"):
            return
        if self.gamble_open:
            self.gamble_open = False
            return
        if self.gold <= 0:
            return
        self.selected_tool = None
        self.selected_tower = None
        self.gamble_open = True
        self.gamble_phase = "choose"
        self.gamble_stake = min(max(self.gamble_stake, 25), self.gold)

    def _click_panel(self, pos):
        if self.state == "prep" and self.hud.action_button.collidepoint(pos):
            self._call_wave()
            return
        for ttype, rect in self.hud.tower_buttons:
            if rect.collidepoint(pos):
                self.selected_tool = ttype
                self.selected_tower = None
                return
        tower = self.selected_tower
        if tower is None:
            if self.hud.gamble_button.collidepoint(pos):
                self._toggle_gamble()
            return
        if tower.can_upgrade() and self.hud.upgrade_button.collidepoint(pos):
            cost = tower.upgrade_cost()
            if self.gold >= cost:
                self.gold -= cost
                tower.upgrade()
            return
        if self.hud.sell_button.collidepoint(pos):
            self.gold += tower.sell_value()
            self.towers.remove(tower)
            self.selected_tower = None

    def _click_field(self, pos):
        cell = self.level.pixel_to_cell(pos[0], pos[1])
        if self.selected_tool is not None:
            self._try_place(cell)
            return
        self.selected_tower = self.tower_at(cell)

    def _try_place(self, cell):
        ttype = self.selected_tool
        if not self.level.is_buildable(cell):
            return
        if self.tower_at(cell) is not None:
            return
        if self.gold < ttype.cost:
            return
        self.gold -= ttype.cost
        self.towers.append(Tower(ttype, cell, cell_center(cell[0], cell[1])))

    def _call_wave(self):
        if self.state != "prep":
            return
        self.gold += max(0, int(self.prep_timer) * EARLY_BONUS_PER_SECOND)
        self._launch_wave()

    def _launch_wave(self):
        self.runner.start(WAVES[self.wave_index])
        self.wave_active = True
        self.state = "playing"
        self.banner = story.ACT_LINES[self.wave_index]

    def _click_gamble(self, pos):
        if self.hud.g_back.collidepoint(pos):
            self.gamble_open = False
            return
        if self.gamble_phase == "result":
            if self.gold > 0 and self.hud.g_again.collidepoint(pos):
                self.gamble_phase = "choose"
                self.gamble_stake = min(max(self.gamble_stake, 25), self.gold)
            return
        if self.gamble_phase != "choose":
            return
        for value, rect in self.hud.g_chips:
            if rect.collidepoint(pos):
                self.gamble_stake = self.gold if value == "ALL" else min(value, self.gold)
                return
        if self.gamble_stake <= 0 or self.gamble_stake > self.gold:
            return
        for (mult, chance, name), rect in self.hud.g_mults:
            if rect.collidepoint(pos):
                self._start_spin(mult, chance)
                return

    def _start_spin(self, mult, chance):
        self.gamble_mult = mult
        self.gamble_chance = chance
        self.gamble_phase = "spinning"
        self.gamble_spin_t = 1.1
        self.gamble_reel_t = 0.0
        self.gamble_reel = "?"

    def _resolve_spin(self):
        stake = self.gamble_stake
        if random.random() < self.gamble_chance:
            gain = stake * (self.gamble_mult - 1)
            self.gold += gain
            self.gamble_delta = gain
        else:
            self.gold -= stake
            self.gamble_delta = -stake
        if self.gold < 0:
            self.gold = 0
        self.gamble_phase = "result"

    def update(self, dt):
        self.pointer = self.to_design(pygame.mouse.get_pos())
        self.time += dt
        if self.state == "title":
            self._update_title(dt)
            return
        if self.gamble_open:
            self._update_gamble(dt)
            return
        if self.state == "prep":
            self._update_prep(dt)
        elif self.state == "playing":
            self._update_world(dt)

    def _update_title(self, dt):
        self.title_spawn_t -= dt
        if self.title_spawn_t <= 0 and len(self.title_enemies) < 7:
            self.title_enemies.append(Enemy(self.path, random.choice([HUSK, STRIDER, JUGGERNAUT])))
            self.title_spawn_t = 1.3
        for enemy in self.title_enemies:
            enemy.update(dt)
        self.title_enemies = [e for e in self.title_enemies if not e.reached_end]
        for spark in self.embers:
            spark["x"] += spark["vx"] * dt
            spark["y"] += spark["vy"] * dt
            spark["life"] -= dt
            if spark["life"] <= 0 or spark["y"] < -8:
                spark.update(self._spawn_ember(False))

    def _update_gamble(self, dt):
        if self.gamble_phase != "spinning":
            return
        self.gamble_spin_t -= dt
        self.gamble_reel_t -= dt
        if self.gamble_reel_t <= 0:
            self.gamble_reel = random.choice(["2x", "3x", "4x", "X", "?", "$"])
            self.gamble_reel_t = 0.06
        if self.gamble_spin_t <= 0:
            self._resolve_spin()

    def _update_prep(self, dt):
        self.prep_timer -= dt
        if self.prep_timer <= 0:
            self.prep_timer = 0
            self._launch_wave()

    def _update_world(self, dt):
        self.runner.update(dt, self.enemies)
        for enemy in self.enemies:
            enemy.update(dt)
        for tower in self.towers:
            tower.update(dt, self.enemies, self.projectiles)
        for shot in self.projectiles:
            shot.update(dt, self.enemies)
        self._resolve()
        if self.lives <= 0:
            self.lives = 0
            self.state = "defeat"
            return
        if self.wave_active and self.runner.done_spawning() and not self.enemies:
            self._finish_wave()

    def _resolve(self):
        survivors = []
        for enemy in self.enemies:
            if enemy.reached_end:
                self.lives -= enemy.damage
            elif enemy.dead:
                if not enemy.rewarded:
                    self.gold += enemy.reward
                    enemy.rewarded = True
            else:
                survivors.append(enemy)
        self.enemies = survivors
        self.projectiles = [shot for shot in self.projectiles if not shot.dead]

    def _finish_wave(self):
        self.wave_active = False
        self.gold += 45 + self.wave_index * 12
        self.wave_index += 1
        if self.wave_index >= self.total_waves:
            self.state = "victory"
        else:
            self.state = "prep"
            self.prep_timer = PREP_BETWEEN
            self.banner = story.ACT_LINES[self.wave_index]

    def draw(self):
        if self.state == "title":
            ui.draw_title(self.screen, self.fonts, self)
        else:
            self.screen.fill(COLOR_BG)
            self.level.draw(self.screen)
            for tower in self.towers:
                tower.draw(self.screen, tower is self.selected_tower)
            for enemy in self.enemies:
                enemy.draw(self.screen)
            for shot in self.projectiles:
                shot.draw(self.screen)
            self._draw_ghost()
            self._draw_boss_bar()
            if self.state in ("prep", "playing"):
                self._draw_banner()
            self.hud.draw(self.screen, self)
            if self.state == "victory":
                ui.draw_end(self.screen, self.fonts, "VICTORY", story.VICTORY_TAG, story.VICTORY, story.VICTORY_PROMPT, COLOR_GO)
            elif self.state == "defeat":
                ui.draw_end(self.screen, self.fonts, "THE SANCTUM FALLS", "", story.DEFEAT, story.DEFEAT_PROMPT, COLOR_BAD)
        if self.gamble_open:
            self.hud.draw_gamble(self.screen, self)
        self._present()

    def _present(self):
        scaled = pygame.transform.smoothscale(self.screen, self.scaled_size)
        self.display.fill((0, 0, 0))
        self.display.blit(scaled, self.offset)
        pygame.display.flip()

    def _draw_ghost(self):
        if self.selected_tool is None:
            return
        if self.pointer[0] >= FIELD_WIDTH or self.pointer[0] < 0 or self.pointer[1] < 0:
            return
        cell = self.level.pixel_to_cell(self.pointer[0], self.pointer[1])
        center = cell_center(cell[0], cell[1])
        valid = (
            self.level.is_buildable(cell)
            and self.tower_at(cell) is None
            and self.gold >= self.selected_tool.cost
        )
        color = COLOR_GO if valid else COLOR_BAD
        reach = self.selected_tool.rng
        ring = pygame.Surface((reach * 2, reach * 2), pygame.SRCALPHA)
        pygame.draw.circle(ring, (*color, 26), (reach, reach), reach)
        pygame.draw.circle(ring, (*color, 130), (reach, reach), reach, 2)
        self.screen.blit(ring, (center[0] - reach, center[1] - reach))
        marker = pygame.Surface((42, 42), pygame.SRCALPHA)
        pygame.draw.rect(marker, (*color, 90), pygame.Rect(0, 0, 42, 42), border_radius=8)
        self.screen.blit(marker, (center[0] - 21, center[1] - 21))

    def _draw_banner(self):
        if not self.banner:
            return
        image = self.fonts["banner"].render(self.banner, True, COLOR_TX_HI)
        width = image.get_width() + 44
        x = (FIELD_WIDTH - width) // 2
        strip = pygame.Surface((width, 36), pygame.SRCALPHA)
        strip.fill((8, 10, 15, 195))
        self.screen.blit(strip, (x, 12))
        pygame.draw.line(self.screen, COLOR_ACCENT, (x, 48), (x + width, 48), 1)
        self.screen.blit(image, (x + 22, 19))

    def _draw_boss_bar(self):
        boss = None
        for enemy in self.enemies:
            if enemy.kind.is_boss and not enemy.dead:
                boss = enemy
                break
        if boss is None:
            return
        width = 540
        x = (FIELD_WIDTH - width) // 2
        y = 58
        panel = pygame.Surface((width, 46), pygame.SRCALPHA)
        panel.fill((20, 6, 10, 215))
        self.screen.blit(panel, (x, y))
        ui.draw_text(self.screen, self.fonts["h2"], "MORHAUNT  ·  THE DREAMING ENGINE", (x + width // 2, y + 12), (255, 184, 184), center=True)
        ratio = max(0.0, boss.hp / boss.max_hp)
        bar = pygame.Rect(x + 16, y + 26, width - 32, 10)
        pygame.draw.rect(self.screen, (40, 14, 18), bar)
        pygame.draw.rect(self.screen, (226, 70, 80), pygame.Rect(bar.x, bar.y, int(bar.width * ratio), bar.height))
        pygame.draw.rect(self.screen, (90, 30, 34), bar, 1)
