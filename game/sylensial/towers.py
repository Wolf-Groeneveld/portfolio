import math

import pygame

from .projectiles import Projectile


class TowerType:
    def __init__(
        self,
        key,
        name,
        tagline,
        cost,
        rng,
        damage,
        cooldown,
        bullet_speed,
        body_color,
        bullet_color,
        splash=0,
        slow_factor=1.0,
        slow_time=0.0,
    ):
        self.key = key
        self.name = name
        self.tagline = tagline
        self.cost = cost
        self.rng = rng
        self.damage = damage
        self.cooldown = cooldown
        self.bullet_speed = bullet_speed
        self.body_color = body_color
        self.bullet_color = bullet_color
        self.splash = splash
        self.slow_factor = slow_factor
        self.slow_time = slow_time


SENTINEL = TowerType(
    "sentinel",
    "Sentinel",
    "Steady aim, fast reload.",
    60,
    150,
    9,
    0.5,
    520,
    (96, 134, 196),
    (170, 214, 255),
)

HOWITZER = TowerType(
    "howitzer",
    "Howitzer",
    "Heavy shells, splash blast.",
    120,
    210,
    30,
    1.4,
    360,
    (196, 132, 78),
    (255, 180, 96),
    splash=58,
)

CRYO = TowerType(
    "cryo",
    "Cryo",
    "Chills the horde to a crawl.",
    90,
    132,
    5,
    0.55,
    560,
    (88, 174, 188),
    (170, 244, 255),
    slow_factor=0.5,
    slow_time=1.3,
)

TOWER_TYPES = [SENTINEL, HOWITZER, CRYO]
MAX_LEVEL = 3


class Tower:
    def __init__(self, ttype, cell, center):
        self.type = ttype
        self.cell = cell
        self.pos = pygame.Vector2(center)
        self.level = 1
        self.damage = ttype.damage
        self.rng = ttype.rng
        self.cooldown = ttype.cooldown
        self.timer = 0.0
        self.angle = -90.0
        self.invested = ttype.cost
        self.target = None
        self.recoil = 0.0

    def upgrade_cost(self):
        return int(self.type.cost * (0.75 + 0.5 * self.level))

    def can_upgrade(self):
        return self.level < MAX_LEVEL

    def upgrade(self):
        spent = self.upgrade_cost()
        self.invested += spent
        self.level += 1
        self.damage = int(self.damage * 1.55)
        self.rng = int(self.rng * 1.12)
        self.cooldown *= 0.92

    def sell_value(self):
        return int(self.invested * 0.65)

    def update(self, dt, enemies, projectiles):
        if self.timer > 0:
            self.timer -= dt
        if self.recoil > 0:
            self.recoil -= dt
        self.target = self._acquire(enemies)
        if self.target is None:
            return
        aim = self.target.pos - self.pos
        self.angle = math.degrees(math.atan2(aim.y, aim.x))
        if self.timer <= 0:
            self._fire(projectiles)
            self.timer = self.cooldown
            self.recoil = 0.08

    def _acquire(self, enemies):
        best = None
        best_key = None
        reach = self.rng * self.rng
        for enemy in enemies:
            if enemy.dead or enemy.reached_end:
                continue
            if (enemy.pos - self.pos).length_squared() <= reach:
                key = enemy.progress_key(self.pos)
                if best_key is None or key > best_key:
                    best_key = key
                    best = enemy
        return best

    def _fire(self, projectiles):
        muzzle = self.pos + pygame.Vector2(self.rng, 0).rotate(self.angle).normalize() * 22
        projectiles.append(
            Projectile(
                muzzle,
                self.target,
                self.type.bullet_speed,
                self.damage,
                self.type.bullet_color,
                splash=self.type.splash,
                slow_factor=self.type.slow_factor,
                slow_time=self.type.slow_time,
            )
        )

    def draw(self, surface, selected=False):
        cx, cy = int(self.pos.x), int(self.pos.y)
        if selected:
            self.draw_range(surface)

        tread = (26, 30, 38)
        pygame.draw.rect(surface, (8, 10, 14), pygame.Rect(cx - 19, cy - 17, 38, 34), border_radius=6)
        pygame.draw.rect(surface, tread, pygame.Rect(cx - 19, cy - 17, 9, 34), border_radius=4)
        pygame.draw.rect(surface, tread, pygame.Rect(cx + 10, cy - 17, 9, 34), border_radius=4)
        for i in range(-12, 16, 7):
            pygame.draw.line(surface, (12, 14, 18), (cx - 19, cy + i), (cx - 10, cy + i), 2)
            pygame.draw.line(surface, (12, 14, 18), (cx + 10, cy + i), (cx + 19, cy + i), 2)

        hull = self.type.body_color
        pygame.draw.rect(surface, hull, pygame.Rect(cx - 11, cy - 13, 22, 26), border_radius=5)
        pygame.draw.rect(surface, (250, 250, 250), pygame.Rect(cx - 11, cy - 13, 22, 26), 1, border_radius=5)

        recoil = 4 if self.recoil > 0 else 0
        barrel = pygame.Vector2(20 - recoil, 0).rotate(self.angle)
        base = self.pos + pygame.Vector2(2, 0).rotate(self.angle)
        pygame.draw.line(
            surface,
            (18, 20, 26),
            (int(base.x), int(base.y)),
            (int(base.x + barrel.x), int(base.y + barrel.y)),
            7,
        )
        bright = tuple(min(255, c + 40) for c in hull)
        pygame.draw.circle(surface, bright, (cx, cy), 9)
        pygame.draw.circle(surface, (12, 14, 18), (cx, cy), 9, 2)

        for i in range(self.level):
            pygame.draw.circle(surface, (255, 226, 140), (cx - 6 + i * 6, cy + 19), 2)

    def draw_range(self, surface):
        ring = pygame.Surface((self.rng * 2, self.rng * 2), pygame.SRCALPHA)
        pygame.draw.circle(ring, (118, 198, 255, 28), (self.rng, self.rng), self.rng)
        pygame.draw.circle(ring, (118, 198, 255, 110), (self.rng, self.rng), self.rng, 2)
        surface.blit(ring, (int(self.pos.x - self.rng), int(self.pos.y - self.rng)))
