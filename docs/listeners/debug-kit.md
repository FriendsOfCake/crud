---
title: Debug Kit
layout: default
---

# DebugKit integration

Adds `DebugKit` integration to Crud

The panel contains the following information:

 * The `Crud Action` configuration
 * All `Crud Listeners` configurations
 * The `Crud Component` configuration

Additionally the `DebugKit listener` will add useful DebugKit timing information to the `Timer` panel

# Setup

This listener will only track DebugKit `timers`

For the `DebugKit panel` the panel must be configured manually as shown below.

{% highlight php %}
<?php
class SamplesController extends AppController {

  public $components = [
    'DebugKit.Toolbar' => [
      // Add Crud panel to DebugKit
      'panels' => ['Crud.Crud']
    ],
    'Crud.Crud' => [
      // Load DebugKit listener
      'listeners' => ['DebugKit']
    ];
}
?>
{% endhighlight %}

# Configuration

The listener do not have any configuration options

# Example output

Below is an example of the `DebugKit` panel activated

<img src="https://f.cloud.github.com/assets/22841/768313/4bb04a2c-e89d-11e2-935a-0a1229201014.png" />
