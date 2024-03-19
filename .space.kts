job("Run Tests") {
    container(displayName = "Build ", image = "revolutionbarsgroup.registry.jetbrains.space/p/r-cloud/rbg-docker/build-runner:8.3") {
        shellScript {
            location = "/usr/bin/build_and_push"
        }
    }
}