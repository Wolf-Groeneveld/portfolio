from .enemies import Enemy, HUSK, STRIDER, JUGGERNAUT, MORHAUNT


def group(enemy_type, count, gap, lead=0.0):
    return {"type": enemy_type, "count": count, "gap": gap, "lead": lead}


WAVES = [
    [group(HUSK, 8, 0.85)],
    [group(HUSK, 12, 0.7)],
    [group(HUSK, 8, 0.7), group(STRIDER, 6, 0.5, lead=2.0)],
    [group(HUSK, 16, 0.55)],
    [group(STRIDER, 12, 0.45), group(HUSK, 6, 0.6, lead=2.5)],
    [group(JUGGERNAUT, 3, 2.2), group(HUSK, 12, 0.55, lead=1.0)],
    [group(STRIDER, 18, 0.38)],
    [group(JUGGERNAUT, 5, 1.8), group(STRIDER, 12, 0.4, lead=2.0)],
    [group(HUSK, 20, 0.4), group(STRIDER, 10, 0.45, lead=3.0), group(JUGGERNAUT, 4, 1.6, lead=2.0)],
    [group(MORHAUNT, 1, 0.0, lead=1.5), group(JUGGERNAUT, 6, 1.4, lead=4.0), group(STRIDER, 10, 0.5, lead=2.0)],
]


class WaveRunner:
    def __init__(self, path):
        self.path = path
        self.schedule = []
        self.clock = 0.0
        self.index = 0
        self.running = False

    def start(self, groups):
        self.schedule = []
        moment = 0.0
        for spec in groups:
            moment += spec["lead"]
            for _ in range(spec["count"]):
                self.schedule.append((moment, spec["type"]))
                moment += spec["gap"]
        self.schedule.sort(key=lambda item: item[0])
        self.clock = 0.0
        self.index = 0
        self.running = True

    def update(self, dt, enemies):
        if not self.running:
            return
        self.clock += dt
        while self.index < len(self.schedule) and self.schedule[self.index][0] <= self.clock:
            enemies.append(Enemy(self.path, self.schedule[self.index][1]))
            self.index += 1
        if self.index >= len(self.schedule):
            self.running = False

    def done_spawning(self):
        return not self.running and self.index >= len(self.schedule)
