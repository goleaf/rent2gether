# Host Unresponsive vs No-show

No-show means the guest did not arrive and did not complete check-in.

Host unresponsive means the guest is trying to check in, get access, or solve an urgent access problem, but the host or representative is not responding.

If a guest marks that they arrived, is at the address, is waiting outside, reports an access problem, or reports host not answering, automatic no-show confirmation must stop. `BookingNoShowDetectionService` checks active host-unresponsive cases before allowing confirmation.

If the case is rejected because the host responded in time or instructions were sufficient, no-show can continue only when the guest truly did not arrive and there is no valid access problem.
