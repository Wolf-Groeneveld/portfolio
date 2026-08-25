import pygame


class Projectile:
    def __init__(self, origin, target, speed, damage, color, splash=0, slow_factor=1.0, slow_time=0.0):
        self.pos = pygame.Vector2(origin)
        self.target = target
        self.speed = speed
        self.damage = damage
        self.color = color
        self.splash = splash
        self.slow_factor = slow_factor
        self.slow_time = slow_time
        self.radius = 6 if splash else 4
        self.dead = False
        self.last_aim = pygame.Vector2(target.pos)

    def update(self, dt, enemies):
        if self.target is None or self.target.dead or self.target.reached_end:
            self.dead = True
            return
        self.last_aim = pygame.Vector2(self.target.pos)
        offset = self.target.pos - self.pos
        distance = offset.length()
        travel = self.speed * dt
        if distance <= travel + self.target.radius:
            self._impact(enemies)
            self.dead = True
        else:
            self.pos += offset / distance * travel

    def _impact(self, enemies):
        if self.splash > 0:
            for enemy in enemies:
                if enemy.dead or enemy.reached_end:
                    continue
                if (enemy.pos - self.last_aim).length() <= self.splash:
                    enemy.take_damage(self.damage)
                    if self.slow_time > 0:
                        enemy.apply_slow(self.slow_factor, self.slow_time)
        else:
            self.target.take_damage(self.damage)
            if self.slow_time > 0:
                self.target.apply_slow(self.slow_factor, self.slow_time)

    def draw(self, surface):
        center = (int(self.pos.x), int(self.pos.y))
        if self.splash:
            pygame.draw.circle(surface, (60, 40, 20), center, self.radius + 2)
        pygame.draw.circle(surface, self.color, center, self.radius)
