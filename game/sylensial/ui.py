import math

import pygame

from .config import (
    FIELD_WIDTH,
    PANEL_WIDTH,
    SCREEN_WIDTH,
    SCREEN_HEIGHT,
    COLOR_PANEL_TOP,
    COLOR_PANEL_BOT,
    COLOR_PANEL_EDGE,
    COLOR_HAIR,
    COLOR_CARD,
    COLOR_CARD_HI,
    COLOR_CARD_SEL,
    COLOR_CARD_LINE,
    COLOR_STROKE,
    COLOR_TX_HI,
    COLOR_TX_MID,
    COLOR_TX_LO,
    COLOR_GOLD,
    COLOR_GOLD_DK,
    COLOR_LIFE,
    COLOR_EMBER,
    COLOR_GO,
    COLOR_INFO,
    COLOR_WARN,
    COLOR_BAD,
    COLOR_WIN,
    COLOR_ACCENT,
    GAMBLE_OPTIONS,
    STAKE_CHIPS,
)
from .towers import TOWER_TYPES, MAX_LEVEL
from . import story


def draw_text(surface, font, text, pos, color, center=False, right=False, shadow=False):
    if shadow:
        shade = font.render(text, True, (0, 0, 0))
        rect = shade.get_rect()
        anchor(rect, pos, center, right)
        surface.blit(shade, (rect.x + 2, rect.y + 2))
    image = font.render(text, True, color)
    rect = image.get_rect()
    anchor(rect, pos, center, right)
    surface.blit(image, rect)
    return rect


def anchor(rect, pos, center, right):
    if center:
        rect.center = pos
    elif right:
        rect.topright = pos
    else:
        rect.topleft = pos


def vgradient(surface, rect, top, bottom):
    height = max(1, rect.height)
    for i in range(height):
        ratio = i / height
        color = (
            int(top[0] + (bottom[0] - top[0]) * ratio),
            int(top[1] + (bottom[1] - top[1]) * ratio),
            int(top[2] + (bottom[2] - top[2]) * ratio),
        )
        pygame.draw.line(surface, color, (rect.x, rect.y + i), (rect.right, rect.y + i))


def coin_icon(surface, center, radius):
    pygame.draw.circle(surface, COLOR_GOLD_DK, center, radius + 1)
    pygame.draw.circle(surface, COLOR_GOLD, center, radius)
    pygame.draw.circle(surface, (255, 232, 160), center, radius, 1)
    pygame.draw.circle(surface, COLOR_GOLD_DK, center, max(2, radius - 4), 2)


def heart_icon(surface, center, size, color):
    cx, cy = center
    r = size // 4
    pygame.draw.circle(surface, color, (cx - r, cy - r // 2), r)
    pygame.draw.circle(surface, color, (cx + r, cy - r // 2), r)
    points = [
        (cx - 2 * r, cy - r // 2),
        (cx + 2 * r, cy - r // 2),
        (cx, cy + size // 2),
    ]
    pygame.draw.polygon(surface, color, points)


def button(surface, font, rect, label, pointer, base, hot, text_color, enabled=True, sub=None, sub_font=None, sub_color=None):
    hovered = enabled and rect.collidepoint(pointer)
    fill = hot if hovered else base
    if not enabled:
        fill = COLOR_CARD
    pygame.draw.rect(surface, fill, rect, border_radius=8)
    edge = COLOR_STROKE if hovered else COLOR_CARD_LINE
    pygame.draw.rect(surface, edge, rect, 1, border_radius=8)
    label_color = text_color if enabled else COLOR_TX_LO
    if sub:
        draw_text(surface, font, label, (rect.centerx, rect.centery - 9), label_color, center=True)
        draw_text(surface, sub_font or font, sub, (rect.centerx, rect.centery + 11), sub_color or COLOR_TX_MID, center=True)
    else:
        draw_text(surface, font, label, rect.center, label_color, center=True)
    return hovered


def tank_glyph(surface, center, color, scale=1.0):
    cx, cy = center
    w = int(18 * scale)
    h = int(15 * scale)
    pygame.draw.rect(surface, (12, 14, 18), pygame.Rect(cx - w, cy - h, w * 2, h * 2), border_radius=4)
    pygame.draw.rect(surface, color, pygame.Rect(cx - w + 4, cy - h + 3, w * 2 - 8, h * 2 - 6), border_radius=3)
    pygame.draw.circle(surface, tuple(min(255, c + 36) for c in color), (cx, cy), int(7 * scale))
    pygame.draw.circle(surface, (12, 14, 18), (cx, cy), int(7 * scale), 2)
    pygame.draw.line(surface, (16, 18, 24), (cx, cy), (cx + int(20 * scale), cy), 5)


class HUD:
    def __init__(self, fonts):
        self.fonts = fonts
        px = FIELD_WIDTH + 16
        pw = PANEL_WIDTH - 32
        self.px = px
        self.pw = pw

        self.resource_card = pygame.Rect(px, 96, pw, 72)
        self.wave_card = pygame.Rect(px, 180, pw, 58)

        self.tower_buttons = []
        for i, ttype in enumerate(TOWER_TYPES):
            self.tower_buttons.append((ttype, pygame.Rect(px, 270 + i * 82, pw, 78)))

        self.context_card = pygame.Rect(px, 524, pw, 118)
        half = (pw - 10) // 2
        self.upgrade_button = pygame.Rect(px, 588, half, 44)
        self.sell_button = pygame.Rect(px + pw - half, 588, half, 44)
        self.gamble_button = pygame.Rect(px, 586, pw, 46)
        self.action_button = pygame.Rect(px, 656, pw, 50)

        gw, gh = 620, 470
        gx = (SCREEN_WIDTH - gw) // 2
        gy = (SCREEN_HEIGHT - gh) // 2
        self.g_panel = pygame.Rect(gx, gy, gw, gh)
        chip_w = (gw - 60 - 36) // 4
        chips = []
        labels = list(STAKE_CHIPS) + ["ALL"]
        for i, value in enumerate(labels):
            rect = pygame.Rect(gx + 30 + i * (chip_w + 12), gy + 156, chip_w, 44)
            chips.append((value, rect))
        self.g_chips = chips
        card_w = (gw - 60 - 32) // 3
        mults = []
        for i, option in enumerate(GAMBLE_OPTIONS):
            rect = pygame.Rect(gx + 30 + i * (card_w + 16), gy + 244, card_w, 118)
            mults.append((option, rect))
        self.g_mults = mults
        bw = (gw - 60 - 16) // 2
        self.g_again = pygame.Rect(gx + 30, gy + 386, bw, 46)
        self.g_back = pygame.Rect(gx + gw - 30 - bw, gy + 386, bw, 46)

    def draw(self, surface, game):
        panel = pygame.Rect(FIELD_WIDTH, 0, PANEL_WIDTH, SCREEN_HEIGHT)
        vgradient(surface, panel, COLOR_PANEL_TOP, COLOR_PANEL_BOT)
        pygame.draw.line(surface, COLOR_PANEL_EDGE, (FIELD_WIDTH, 0), (FIELD_WIDTH, SCREEN_HEIGHT), 2)
        pygame.draw.line(surface, COLOR_ACCENT, (FIELD_WIDTH, 0), (FIELD_WIDTH, 120), 2)

        self._draw_brand(surface)
        self._draw_resources(surface, game)
        self._draw_wave(surface, game)
        draw_text(surface, self.fonts["label"], "BUILD  A  TANK", (self.px, 250), COLOR_TX_MID)
        self._draw_towers(surface, game)
        self._draw_context(surface, game)
        self._draw_action(surface, game)

    def _draw_brand(self, surface):
        px = self.px
        draw_text(surface, self.fonts["brand"], "SYLENSIAL'S", (px, 22), COLOR_TX_HI)
        draw_text(surface, self.fonts["brand"], "NIGHTMARE", (px, 44), COLOR_ACCENT)
        draw_text(surface, self.fonts["label"], "PART ONE  ·  HOLD THE SANCTUM", (px, 70), COLOR_TX_LO)

    def _draw_resources(self, surface, game):
        rect = self.resource_card
        pygame.draw.rect(surface, COLOR_CARD, rect, border_radius=10)
        pygame.draw.rect(surface, COLOR_CARD_LINE, rect, 1, border_radius=10)
        mid = rect.centerx
        pygame.draw.line(surface, COLOR_HAIR, (mid, rect.y + 12), (mid, rect.bottom - 12))

        coin_icon(surface, (rect.x + 24, rect.y + 26), 9)
        draw_text(surface, self.fonts["micro"], "GOLD", (rect.x + 40, rect.y + 16), COLOR_TX_LO)
        draw_text(surface, self.fonts["stat"], str(game.gold), (rect.x + 18, rect.y + 32), COLOR_GOLD)

        heart_icon(surface, (mid + 24, rect.y + 26), 16, COLOR_LIFE)
        draw_text(surface, self.fonts["micro"], "LIVES", (mid + 40, rect.y + 16), COLOR_TX_LO)
        lives_color = COLOR_LIFE if game.lives > 5 else COLOR_BAD
        draw_text(surface, self.fonts["stat"], str(max(0, game.lives)), (mid + 18, rect.y + 32), lives_color)

    def _draw_wave(self, surface, game):
        rect = self.wave_card
        pygame.draw.rect(surface, COLOR_CARD, rect, border_radius=10)
        pygame.draw.rect(surface, COLOR_CARD_LINE, rect, 1, border_radius=10)
        shown = min(game.wave_index + 1, game.total_waves)
        draw_text(surface, self.fonts["micro"], "WAVE", (rect.x + 14, rect.y + 10), COLOR_TX_LO)
        draw_text(surface, self.fonts["h2"], "{} / {}".format(shown, game.total_waves), (rect.x + 14, rect.y + 24), COLOR_TX_HI)
        status, status_color = self._wave_status(game)
        draw_text(surface, self.fonts["small"], status, (rect.right - 14, rect.y + 14), status_color, right=True)

        total = game.total_waves
        seg_w = (rect.width - 28 - (total - 1) * 4) / total
        for i in range(total):
            x = rect.x + 14 + i * (seg_w + 4)
            seg = pygame.Rect(int(x), rect.bottom - 14, int(seg_w), 5)
            if i < game.wave_index:
                color = COLOR_GO
            elif i == game.wave_index and game.state != "victory":
                pulse = 0.55 + 0.45 * math.sin(game.time * 6)
                color = tuple(int(c * pulse) for c in COLOR_EMBER)
            else:
                color = COLOR_CARD_LINE
            pygame.draw.rect(surface, color, seg, border_radius=2)

    def _wave_status(self, game):
        if game.state == "prep":
            return "DEPLOYS IN {}s".format(int(game.prep_timer) + 1), COLOR_WARN
        if game.state == "playing":
            left = len([e for e in game.enemies if not e.dead and not e.reached_end]) + game.spawns_pending()
            return "{} HOSTILE".format(left), COLOR_EMBER
        if game.state == "victory":
            return "CLEARED", COLOR_GO
        return "", COLOR_TX_LO

    def _draw_towers(self, surface, game):
        for ttype, rect in self.tower_buttons:
            affordable = game.gold >= ttype.cost
            selected = game.selected_tool is ttype
            hovered = rect.collidepoint(game.pointer)
            if selected:
                fill, edge = COLOR_CARD_SEL, COLOR_ACCENT
            elif hovered and affordable:
                fill, edge = COLOR_CARD_HI, COLOR_STROKE
            else:
                fill, edge = COLOR_CARD, COLOR_CARD_LINE
            pygame.draw.rect(surface, fill, rect, border_radius=10)
            pygame.draw.rect(surface, edge, rect, 2 if selected else 1, border_radius=10)

            tank_glyph(surface, (rect.x + 30, rect.y + 28), ttype.body_color)

            name_color = COLOR_TX_HI if affordable else COLOR_TX_LO
            draw_text(surface, self.fonts["h2"], ttype.name, (rect.x + 58, rect.y + 12), name_color)
            coin_icon(surface, (rect.right - 52, rect.y + 21), 7)
            draw_text(surface, self.fonts["h2"], str(ttype.cost), (rect.right - 14, rect.y + 12), COLOR_GOLD if affordable else COLOR_BAD, right=True)
            draw_text(surface, self.fonts["small"], ttype.tagline, (rect.x + 58, rect.y + 36), COLOR_TX_MID)
            chips = "DMG {}    RNG {}    {:.2f}s".format(ttype.damage, ttype.rng, ttype.cooldown)
            draw_text(surface, self.fonts["micro"], chips, (rect.x + 58, rect.y + 56), COLOR_TX_LO)

    def _draw_context(self, surface, game):
        rect = self.context_card
        pygame.draw.rect(surface, COLOR_CARD, rect, border_radius=10)
        pygame.draw.rect(surface, COLOR_CARD_LINE, rect, 1, border_radius=10)
        tower = game.selected_tower
        if tower is None:
            self._draw_gamble_entry(surface, game, rect)
            return

        draw_text(surface, self.fonts["h2"], tower.type.name, (rect.x + 14, rect.y + 12), COLOR_TX_HI)
        draw_text(surface, self.fonts["small"], "LEVEL {}/{}".format(tower.level, MAX_LEVEL), (rect.right - 14, rect.y + 14), COLOR_ACCENT, right=True)
        draw_text(surface, self.fonts["small"], "DMG {}    RNG {}    {:.2f}s".format(tower.damage, tower.rng, tower.cooldown), (rect.x + 14, rect.y + 38), COLOR_TX_MID)

        if tower.can_upgrade():
            cost = tower.upgrade_cost()
            ok = game.gold >= cost
            button(surface, self.fonts["small"], self.upgrade_button, "UPGRADE", game.pointer, COLOR_CARD_SEL, COLOR_CARD_HI, COLOR_TX_HI, enabled=ok, sub="{} gold".format(cost), sub_font=self.fonts["micro"], sub_color=COLOR_GOLD)
        else:
            pygame.draw.rect(surface, COLOR_CARD_HI, self.upgrade_button, border_radius=8)
            draw_text(surface, self.fonts["small"], "MAX LEVEL", self.upgrade_button.center, COLOR_GO, center=True)
        button(surface, self.fonts["small"], self.sell_button, "SELL", game.pointer, COLOR_CARD_HI, COLOR_CARD_SEL, COLOR_TX_HI, sub="+{} gold".format(tower.sell_value()), sub_font=self.fonts["micro"], sub_color=COLOR_GOLD)

    def _draw_gamble_entry(self, surface, game, rect):
        draw_text(surface, self.fonts["small"], "Select a tank to deploy, or take", (rect.x + 14, rect.y + 12), COLOR_TX_MID)
        draw_text(surface, self.fonts["small"], "your chances at the war coffers.", (rect.x + 14, rect.y + 31), COLOR_TX_MID)
        enabled = game.gold > 0
        button(surface, self.fonts["h2"], self.gamble_button, "RISK  THE  COFFERS", game.pointer, (52, 40, 22), (74, 56, 30), COLOR_GOLD, enabled=enabled)

    def _draw_action(self, surface, game):
        rect = self.action_button
        if game.state == "prep":
            hovered = button(surface, self.fonts["h2"], rect, "DEPLOY  NOW", game.pointer, COLOR_ACCENT, (150, 210, 255), (10, 14, 22))
            draw_text(surface, self.fonts["micro"], "early call rewards gold", (rect.centerx, rect.bottom + 1), COLOR_TX_LO, center=True)
        elif game.state == "playing":
            pygame.draw.rect(surface, COLOR_CARD, rect, border_radius=10)
            pygame.draw.rect(surface, COLOR_CARD_LINE, rect, 1, border_radius=10)
            draw_text(surface, self.fonts["h2"], "WAVE {} ENGAGED".format(game.wave_index + 1), rect.center, COLOR_EMBER, center=True)
        else:
            pygame.draw.rect(surface, COLOR_CARD, rect, border_radius=10)
            draw_text(surface, self.fonts["h2"], "STANDING DOWN", rect.center, COLOR_TX_MID, center=True)

    def draw_gamble(self, surface, game):
        veil = pygame.Surface((SCREEN_WIDTH, SCREEN_HEIGHT), pygame.SRCALPHA)
        veil.fill((4, 5, 9, 232))
        surface.blit(veil, (0, 0))
        panel = self.g_panel
        vgradient(surface, panel, (26, 22, 16), (14, 12, 10))
        pygame.draw.rect(surface, COLOR_GOLD_DK, panel, 2, border_radius=14)

        draw_text(surface, self.fonts["title"], "RISK THE COFFERS", (panel.centerx, panel.y + 30), COLOR_GOLD, center=True)
        draw_text(surface, self.fonts["small"], "fortune favours the desperate warden", (panel.centerx, panel.y + 58), COLOR_TX_MID, center=True)

        pill = pygame.Rect(panel.centerx - 70, panel.y + 80, 140, 30)
        pygame.draw.rect(surface, (10, 11, 15), pill, border_radius=15)
        pygame.draw.rect(surface, COLOR_GOLD_DK, pill, 1, border_radius=15)
        coin_icon(surface, (pill.x + 20, pill.centery), 8)
        draw_text(surface, self.fonts["h2"], str(game.gold), (pill.x + 36, pill.centery - 9), COLOR_GOLD)

        draw_text(surface, self.fonts["label"], "CHOOSE YOUR STAKE", (panel.x + 30, panel.y + 134), COLOR_TX_MID)
        for value, rect in self.g_chips:
            stake = game.gold if value == "ALL" else value
            usable = game.gold > 0 and stake <= game.gold and stake > 0
            selected = game.gamble_stake == stake and stake > 0
            fill = COLOR_CARD_SEL if selected else COLOR_CARD
            if not usable:
                fill = (18, 18, 22)
            pygame.draw.rect(surface, fill, rect, border_radius=8)
            pygame.draw.rect(surface, COLOR_GOLD if selected else COLOR_CARD_LINE, rect, 1, border_radius=8)
            label = "ALL IN" if value == "ALL" else str(value)
            color = COLOR_GOLD if usable else COLOR_TX_LO
            draw_text(surface, self.fonts["h2"], label, rect.center, color, center=True)

        draw_text(surface, self.fonts["label"], "PICK YOUR RISK", (panel.x + 30, panel.y + 222), COLOR_TX_MID)
        if game.gamble_phase == "choose":
            self._draw_mult_cards(surface, game)
        else:
            self._draw_reel(surface, game)

        if game.gamble_phase == "result":
            button(surface, self.fonts["h2"], self.g_again, "GO AGAIN", game.pointer, COLOR_CARD_SEL, COLOR_CARD_HI, COLOR_TX_HI, enabled=game.gold > 0)
        draw_text(surface, self.fonts["micro"], "stake returns multiplied on a win  ·  lost entirely on a bust", (panel.centerx, self.g_back.y - 16), COLOR_TX_LO, center=True)
        button(surface, self.fonts["h2"], self.g_back, "BACK TO BATTLE", game.pointer, COLOR_CARD, COLOR_CARD_HI, COLOR_TX_HI)

    def _draw_mult_cards(self, surface, game):
        ready = game.gamble_stake > 0 and game.gamble_stake <= game.gold
        for (mult, chance, name), rect in self.g_mults:
            hovered = ready and rect.collidepoint(game.pointer)
            fill = COLOR_CARD_HI if hovered else COLOR_CARD
            if not ready:
                fill = (18, 18, 22)
            pygame.draw.rect(surface, fill, rect, border_radius=10)
            pygame.draw.rect(surface, COLOR_GOLD_DK if ready else COLOR_CARD_LINE, rect, 1, border_radius=10)
            draw_text(surface, self.fonts["h1"], "{}x".format(mult), (rect.centerx, rect.y + 16), COLOR_GOLD if ready else COLOR_TX_LO, center=True)
            draw_text(surface, self.fonts["small"], name, (rect.centerx, rect.y + 52), COLOR_TX_HI if ready else COLOR_TX_LO, center=True)
            draw_text(surface, self.fonts["micro"], "{}% odds".format(int(chance * 100)), (rect.centerx, rect.y + 74), COLOR_TX_MID, center=True)
            payout = game.gamble_stake * mult
            draw_text(surface, self.fonts["micro"], "win {}".format(payout), (rect.centerx, rect.y + 92), COLOR_GO if ready else COLOR_TX_LO, center=True)

    def _draw_reel(self, surface, game):
        area = pygame.Rect(self.g_mults[0][1].x, self.g_mults[0][1].y, self.g_mults[2][1].right - self.g_mults[0][1].x, 118)
        pygame.draw.rect(surface, (10, 11, 15), area, border_radius=10)
        pygame.draw.rect(surface, COLOR_GOLD_DK, area, 1, border_radius=10)
        if game.gamble_phase == "spinning":
            draw_text(surface, self.fonts["display"], game.gamble_reel, area.center, COLOR_GOLD, center=True)
            draw_text(surface, self.fonts["small"], "rolling the bones...", (area.centerx, area.bottom - 20), COLOR_TX_MID, center=True)
        else:
            won = game.gamble_delta > 0
            color = COLOR_WIN if won else COLOR_BAD
            headline = "WIN" if won else "BUST"
            draw_text(surface, self.fonts["display"], headline, (area.centerx, area.centery - 14), color, center=True)
            sign = "+" if won else ""
            draw_text(surface, self.fonts["h1"], "{}{} gold".format(sign, game.gamble_delta), (area.centerx, area.centery + 26), color, center=True)


def draw_title(surface, fonts, game):
    surface.fill((6, 8, 12))
    game.level.draw(surface)
    panel = pygame.Rect(FIELD_WIDTH, 0, PANEL_WIDTH, SCREEN_HEIGHT)
    vgradient(surface, panel, COLOR_PANEL_TOP, COLOR_PANEL_BOT)
    pygame.draw.line(surface, COLOR_PANEL_EDGE, (FIELD_WIDTH, 0), (FIELD_WIDTH, SCREEN_HEIGHT), 2)
    veil = pygame.Surface((SCREEN_WIDTH, SCREEN_HEIGHT), pygame.SRCALPHA)
    veil.fill((7, 9, 14, 198))
    surface.blit(veil, (0, 0))
    for enemy in game.title_enemies:
        enemy.draw(surface)
    for spark in game.embers:
        alpha = max(0, int(180 * (spark["life"] / spark["max"])))
        glow = pygame.Surface((6, 6), pygame.SRCALPHA)
        pygame.draw.circle(glow, (244, 150, 70, alpha), (3, 3), 3)
        surface.blit(glow, (int(spark["x"]), int(spark["y"])))

    cx = SCREEN_WIDTH // 2
    draw_text(surface, fonts["mega"], "SYLENSIAL'S", (cx, 196), COLOR_TX_HI, center=True, shadow=True)
    draw_text(surface, fonts["mega"], "NIGHTMARE", (cx, 268), COLOR_ACCENT, center=True, shadow=True)
    line = pygame.Rect(cx - 150, 318, 300, 2)
    pygame.draw.rect(surface, COLOR_GOLD_DK, line)
    draw_text(surface, fonts["big"], "PART ONE", (cx, 344), COLOR_GOLD, center=True)
    draw_text(surface, fonts["body"], "Hold the road. Break the Rusthorde. Outlast Morhaunt.", (cx, 392), COLOR_TX_MID, center=True)

    pulse = 0.5 + 0.5 * math.sin(game.time * 3.2)
    prompt_color = (int(120 + 115 * pulse), int(170 + 70 * pulse), int(220 + 35 * pulse))
    draw_text(surface, fonts["h1"], "PRESS  ENTER  TO  DEPLOY", (cx, 470), prompt_color, center=True)
    draw_text(surface, fonts["small"], "mouse to build  ·  G to gamble  ·  Esc to quit", (cx, 512), COLOR_TX_LO, center=True)
    draw_text(surface, fonts["micro"], "a tower defence in three acts  ·  this is act one", (cx, SCREEN_HEIGHT - 40), COLOR_GOLD_DK, center=True)


def draw_end(surface, fonts, title, subtitle, lines, prompt, title_color):
    veil = pygame.Surface((SCREEN_WIDTH, SCREEN_HEIGHT), pygame.SRCALPHA)
    veil.fill((6, 8, 13, 230))
    surface.blit(veil, (0, 0))
    cx = SCREEN_WIDTH // 2
    draw_text(surface, fonts["mega"], title, (cx, 150), title_color, center=True, shadow=True)
    if subtitle:
        draw_text(surface, fonts["big"], subtitle, (cx, 214), COLOR_TX_MID, center=True)
    y = 286
    for line in lines:
        draw_text(surface, fonts["body"], line, (cx, y), COLOR_TX_HI, center=True)
        y += 34
    draw_text(surface, fonts["h2"], prompt, (cx, y + 26), COLOR_ACCENT, center=True)
